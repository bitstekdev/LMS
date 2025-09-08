<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\LiveClass;
use Illuminate\Support\Facades\Session;

class LiveClassController extends Controller
{
    public function live_class_join($id)
    {
        // Validate that ID is numeric
        if (! is_numeric($id)) {
            abort(404, 'Invalid class ID');
        }

        // Find the live class or fail gracefully
        $live_class = LiveClass::find($id);

        if (! $live_class) {
            Session::flash('error', get_phrase('Live class not found.'));

            return redirect()->back();
        }

        // Handle Zoom and other providers
        if ($live_class->provider === 'zoom') {
            if (get_settings('zoom_web_sdk') === 'active') {
                return view('course_player.live_class.zoom_live_class', [
                    'live_class' => $live_class,
                ]);
            } else {
                $meeting_info = json_decode($live_class->additional_info, true);

                if (! isset($meeting_info['start_url'])) {
                    Session::flash('error', get_phrase('Zoom meeting URL not found.'));

                    return redirect()->back();
                }

                return redirect($meeting_info['start_url']);
            }
        }

        // Fallback view for other providers
        return view('course_player.live_class.zoom_live_class', [
            'live_class' => $live_class,
        ]);
    }
}
