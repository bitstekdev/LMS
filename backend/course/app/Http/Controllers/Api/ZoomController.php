<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LiveClass;
use Illuminate\Http\Request;

class ZoomController extends Controller
{
    public function zoom_settings()
    {
        return [
            'zoom_sdk' => get_settings('zoom_web_sdk'),
            'zoom_sdk_client_id' => get_settings('zoom_sdk_client_id'),
            'zoom_sdk_client_secret' => get_settings('zoom_sdk_client_secret'),
        ];
    }

    public function live_class_schedules(Request $request)
    {
        $classes = [];
        $live_classes = LiveClass::where('course_id', $request->course_id)->orderBy('class_date_and_time', 'desc')->get();

        foreach ($live_classes as $live_class) {
            $info = json_decode($live_class->additional_info, true);
            $classes[] = [
                'class_topic' => $live_class->class_topic,
                'provider' => $live_class->provider,
                'note' => $live_class->note,
                'class_date_and_time' => $live_class->class_date_and_time,
                'meeting_id' => $info['id'] ?? null,
                'meeting_password' => $info['password'] ?? null,
                'start_url' => $info['start_url'] ?? null,
                'join_url' => $info['join_url'] ?? null,
            ];
        }

        return [
            'live_classes' => $classes,
        ];
    }
}
