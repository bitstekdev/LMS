<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\BootcampModule;
use App\Models\BootcampResource;
use App\Services\FileUploaderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BootcampResourceController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'module_id' => 'required|numeric|exists:bootcamp_modules,id',
            'upload_type' => 'required|in:resource,record',
            'files' => 'required|array',
            'files.*' => 'required|file',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $module = BootcampModule::with('bootcamp')->find($request->module_id);
        if (! $module || ! $module->bootcamp) {
            return back()->with('error', get_phrase('Module not found.'));
        }

        $bootcampTitle = $module->bootcamp->title;
        $moduleTitle = $module->title;
        $uploadType = $request->upload_type;

        $allowedVideoExtensions = ['mp4', 'mov', 'avi', 'wmv', 'webm'];

        foreach ($request->file('files') as $file) {
            $extension = strtolower($file->getClientOriginalExtension());

            // Validate file type for 'record'
            if ($uploadType === 'record' && ! in_array($extension, $allowedVideoExtensions)) {
                return back()->with('error', get_phrase('Only video files are allowed for recording uploads.'));
            }

            $originalName = replace_url_symbol($file->getClientOriginalName());
            $filePath = 'uploads/bootcamp/resource/'.auth('web')->user()->name.'/'.$bootcampTitle.'/'.$moduleTitle.'/'.$originalName;

            // Prevent duplicate title
            $duplicate = BootcampResource::where('title', $originalName)
                ->where('module_id', $module->id)
                ->where('upload_type', $uploadType)
                ->exists();

            if ($duplicate) {
                return back()->with('error', get_phrase('File already exists.'));
            }

            // Save to DB
            BootcampResource::create([
                'module_id' => $module->id,
                'upload_type' => $uploadType,
                'title' => $originalName,
                'file' => $filePath,
            ]);

            // Upload file
            app(FileUploaderService::class)->upload($file, $filePath);
        }

        return back()->with('success', get_phrase(ucfirst($uploadType).' has been uploaded successfully.'));
    }

    public function delete($id)
    {
        $resource = BootcampResource::find($id);

        if (! $resource) {
            return back()->with('error', get_phrase('Resource not found.'));
        }

        $filePath = public_path($resource->file);

        if (file_exists($filePath)) {
            remove_file($filePath);
        }

        $resource->delete();

        return back()->with('success', get_phrase(ucfirst($resource->upload_type).' has been deleted.'));
    }

    public function download($id)
    {
        $resource = BootcampResource::find($id);

        if (! $resource) {
            return back()->with('error', get_phrase('Resource not found.'));
        }

        $filePath = public_path($resource->file);

        if (! file_exists($filePath)) {
            return back()->with('error', get_phrase('File does not exist.'));
        }

        return response()->download($filePath);
    }
}
