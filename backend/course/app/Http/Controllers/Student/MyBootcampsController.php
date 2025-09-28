<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\BootcampLiveClass;
use App\Models\BootcampResource;
use Illuminate\Support\Facades\Session;

class MyBootcampsController extends Controller
{
    public function join_class($slug)
    {
        $current_time = time();

        $class = BootcampLiveClass::join('bootcamp_modules', 'bootcamp_live_classes.module_id', 'bootcamp_modules.id')
            ->join('bootcamps', 'bootcamp_modules.bootcamp_id', 'bootcamps.id')
            ->join('bootcamp_purchases', function ($join) {
                $join->on('bootcamps.id', '=', 'bootcamp_purchases.bootcamp_id')
                    ->where('bootcamp_purchases.user_id', auth('web')->id())
                    ->where('bootcamp_purchases.status', 1);
            })
            ->where('bootcamp_live_classes.slug', $slug)
            ->select('bootcamp_live_classes.*', 'bootcamps.id as bootcamp_id', 'bootcamp_purchases.user_id as enrolled_user')
            ->first();

        if (! $class) {
            Session::flash('error', get_phrase('Class not found.'));

            return redirect()->back();
        }

        if ($current_time > $class->end_time) {
            Session::flash('error', get_phrase('Time up! Class is over.'));

            return redirect()->back();
        }

        if (get_settings('zoom_web_sdk') === 'active') {
            return view('bootcamp_online_class.index', [
                'class' => $class,
                'user' => get_user_info($class->enrolled_user),
                'is_host' => 0,
            ]);
        }

        $meeting_info = json_decode($class->joining_data, true);

        if (! isset($meeting_info['start_url'])) {
            Session::flash('error', get_phrase('Joining link not found.'));

            return redirect()->back();
        }

        return redirect($meeting_info['start_url']);
    }

    public function download($id)
    {
        $resource = BootcampResource::join('bootcamp_modules', 'bootcamp_resources.module_id', 'bootcamp_modules.id')
            ->join('bootcamps', 'bootcamp_modules.bootcamp_id', 'bootcamps.id')
            ->join('bootcamp_purchases', 'bootcamps.id', 'bootcamp_purchases.bootcamp_id')
            ->where('bootcamp_resources.id', $id)
            ->where('bootcamp_resources.upload_type', 'resource')
            ->where('bootcamp_purchases.user_id', auth('web')->id())
            ->select('bootcamp_resources.*')
            ->first();

        if (! $resource) {
            Session::flash('error', get_phrase('Data not found.'));

            return redirect()->back();
        }

        $file_path = public_path($resource->file);

        if (! file_exists($file_path)) {
            Session::flash('error', get_phrase('File does not exist.'));

            return redirect()->back();
        }

        return response()->download($file_path);
    }

    public function play($file)
    {
        $class = BootcampResource::join('bootcamp_modules', 'bootcamp_resources.module_id', 'bootcamp_modules.id')
            ->join('bootcamps', 'bootcamp_modules.bootcamp_id', 'bootcamps.id')
            ->join('bootcamp_purchases', 'bootcamps.id', 'bootcamp_purchases.bootcamp_id')
            ->where('bootcamp_resources.title', $file)
            ->where('bootcamp_resources.upload_type', 'record')
            ->where('bootcamp_purchases.user_id', auth('web')->id())
            ->select('bootcamp_resources.*', 'bootcamps.slug as bootcamp_slug')
            ->first();

        if (! $class) {
            Session::flash('error', get_phrase('Data not found.'));

            return redirect()->back();
        }

        $file_path = public_path($class->file);

        if (! file_exists($file_path)) {
            Session::flash('error', get_phrase('File does not exist.'));

            return redirect()->back();
        }

        return view('class_record.player', compact('class'));
    }
}
