<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class FileUploaderService
{
    protected $imageManager;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver);
    }

    public function upload($uploadedFile, $uploadTo, $width = null, $height = null, $optimizedWidth = 250, $optimizedHeight = null)
    {
        if (! $uploadedFile) {
            return null;
        }

        if (! extension_loaded('fileinfo')) {
            Session::flash('error', 'Please enable fileinfo extension on your server.');

            return null;
        }

        if (! extension_loaded('exif')) {
            Session::flash('error', 'Please enable exif extension on your server.');

            return null;
        }

        if (! str_contains($uploadTo, 'http') && str_contains($uploadTo, 'public')) {
            $uploadTo = str_replace('public/', '', $uploadTo);
        }

        $uploadPath = $uploadTo;
        $fullUploadPath = public_path($uploadTo);

        $s3Keys = get_settings('amazon_s3', 'object');

        if (empty($s3Keys) || $s3Keys->active != 1) {
            // Local storage
            $fileName = time().'-'.bin2hex(random_bytes(8)).'.'.$uploadedFile->getClientOriginalExtension();

            if (! is_dir($fullUploadPath)) {
                mkdir($fullUploadPath, 0755, true);
            }

            if (is_null($width)) {
                $uploadedFile->move($fullUploadPath, $fileName);
            } else {
                // Main image resize
                $this->imageManager
                    ->read($uploadedFile->getRealPath())
                    ->scale(width: $width, height: $height)
                    ->save($fullUploadPath.'/'.$fileName);

                // Optimized resize
                $optimizedPath = $fullUploadPath.'/optimized';

                if (! is_dir($optimizedPath)) {
                    mkdir($optimizedPath, 0755, true);
                }

                $this->imageManager
                    ->read($uploadedFile->getRealPath())
                    ->scale(width: $optimizedWidth, height: $optimizedHeight)
                    ->save($optimizedPath.'/'.$fileName);
            }

            return $uploadPath.'/'.$fileName;
        } else {
            // Amazon S3 Upload
            ini_set('max_execution_time', '600');

            config(['filesystems.disks.s3.key' => $s3Keys->AWS_ACCESS_KEY_ID]);
            config(['filesystems.disks.s3.secret' => $s3Keys->AWS_SECRET_ACCESS_KEY]);
            config(['filesystems.disks.s3.region' => $s3Keys->AWS_DEFAULT_REGION]);
            config(['filesystems.disks.s3.bucket' => $s3Keys->AWS_BUCKET]);

            $s3FilePath = Storage::disk('s3')->put('social-files', $uploadedFile, 'public');

            return Storage::url($s3FilePath);
        }
    }
}
