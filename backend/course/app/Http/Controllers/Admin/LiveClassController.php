<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LiveClass;
use App\Models\Setting;
use Illuminate\Http\Request;

class LiveClassController extends Controller
{
    public function live_class_start($id)
    {
        $live_class = LiveClass::findOrFail($id);

        if ($live_class->provider === 'zoom') {
            if (get_settings('zoom_web_sdk') === 'active') {
                return view('course_player.live_class.zoom_live_class', compact('live_class'));
            } else {
                $meeting_info = json_decode($live_class->additional_info, true);

                return redirect($meeting_info['start_url']);
            }
        }

        return view('course_player.live_class.zoom_live_class', compact('live_class'));
    }

    public function live_class_store(Request $request, $course_id)
    {
        $request->validate([
            'class_topic' => 'required|max:255',
            'class_date_and_time' => 'required|date',
            'user_id' => 'required|integer',
        ]);

        $data = $request->only(['class_topic', 'user_id', 'provider', 'note']);
        $data['course_id'] = $course_id;
        $data['class_date_and_time'] = date('Y-m-d\TH:i:s', strtotime($request->class_date_and_time));

        if ($request->provider === 'zoom') {
            $meeting_info = $this->create_zoom_live_class($request->class_topic, $request->class_date_and_time);
            $meeting_info_arr = json_decode($meeting_info, true);

            if (isset($meeting_info_arr['code'])) {
                return redirect()->route('admin.course.edit', ['id' => $course_id, 'tab' => 'live-class'])
                    ->with('error', get_phrase($meeting_info_arr['message']));
            }

            $data['additional_info'] = $meeting_info;
        }

        LiveClass::create($data);

        return redirect()->route('admin.course.edit', ['id' => $course_id, 'tab' => 'live-class'])
            ->with('success', get_phrase('Live class added successfully'));
    }

    public function live_class_update(Request $request, $id)
    {
        $live_class = LiveClass::findOrFail($id);

        $request->validate([
            'class_topic' => 'required|max:255',
            'class_date_and_time' => 'required|date',
            'user_id' => 'required|integer',
        ]);

        $data = $request->only(['class_topic', 'user_id', 'note']);
        $data['class_date_and_time'] = date('Y-m-d\TH:i:s', strtotime($request->class_date_and_time));

        if ($live_class->provider === 'zoom') {
            $meeting_info = json_decode($live_class->additional_info, true);
            $this->update_zoom_live_class($request->class_topic, $request->class_date_and_time, $meeting_info['id']);

            $meeting_info['start_time'] = $data['class_date_and_time'];
            $meeting_info['topic'] = $request->class_topic;

            $data['additional_info'] = json_encode($meeting_info);
        }

        $live_class->update($data);

        return redirect()->route('admin.course.edit', ['id' => $live_class->course_id, 'tab' => 'live-class'])
            ->with('success', get_phrase('Live class updated successfully'));
    }

    public function live_class_delete($id)
    {
        $live_class = LiveClass::findOrFail($id);
        $course_id = $live_class->course_id;

        if ($live_class->provider === 'zoom' && $live_class->additional_info) {
            $meeting_info = json_decode($live_class->additional_info, true);
            $this->delete_zoom_live_class($meeting_info['id']);
        }

        $live_class->delete();

        return redirect()->route('admin.course.edit', ['id' => $course_id, 'tab' => 'live-class'])
            ->with('success', get_phrase('Live class deleted successfully'));
    }

    public function live_class_settings()
    {
        return view('admin.setting.live_class_settings');
    }

    public function update_live_class_settings(Request $request)
    {
        $request->validate([
            'zoom_account_email' => 'required|email',
            'zoom_web_sdk' => 'required|in:active,inactive',
            'zoom_account_id' => 'required|string',
            'zoom_client_id' => 'required|string',
            'zoom_client_secret' => 'required|string',
        ]);

        foreach ($request->only([
            'zoom_account_email',
            'zoom_web_sdk',
            'zoom_account_id',
            'zoom_client_id',
            'zoom_client_secret',
        ]) as $type => $description) {
            Setting::updateOrCreate(['type' => $type], ['description' => $description]);
        }

        return redirect()->route('admin.live.class.settings')
            ->with('success', get_phrase('Zoom live class settings have been configured.'));
    }

    private function create_zoom_live_class($topic, $date_time)
    {
        $token = $this->create_zoom_token();
        $zoomEndpoint = 'https://api.zoom.us/v2/users/me/meetings';

        $meetingData = [
            'topic' => $topic,
            'type' => 2,
            'start_time' => gmdate('Y-m-d\TH:i:s\Z', strtotime($date_time)),
            'duration' => 60,
            'timezone' => get_settings('timezone') ?? 'UTC',
            'settings' => [
                'approval_type' => 2,
                'join_before_host' => true,
            ],
        ];

        $headers = [
            'Authorization: Bearer '.$token,
            'Content-Type: application/json',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $zoomEndpoint);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($meetingData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    private function update_zoom_live_class($topic, $date_time, $meetingId)
    {
        $token = $this->create_zoom_token();
        $zoomEndpoint = 'https://api.zoom.us/v2/meetings/'.$meetingId;

        $meetingData = [
            'topic' => $topic,
            'start_time' => gmdate('Y-m-d\TH:i:s\Z', strtotime($date_time)),
        ];

        $headers = [
            'Authorization: Bearer '.$token,
            'Content-Type: application/json',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $zoomEndpoint);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($meetingData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    private function delete_zoom_live_class($meetingId)
    {
        $token = $this->create_zoom_token();
        $zoomEndpoint = 'https://api.zoom.us/v2/meetings/'.$meetingId;

        $headers = [
            'Authorization: Bearer '.$token,
            'Content-Type: application/json',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $zoomEndpoint);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    private function create_zoom_token()
    {
        $clientId = get_settings('zoom_client_id');
        $clientSecret = get_settings('zoom_client_secret');
        $accountId = get_settings('zoom_account_id');

        $authHeader = 'Basic '.base64_encode("{$clientId}:{$clientSecret}");

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => "https://zoom.us/oauth/token?grant_type=account_credentials&account_id={$accountId}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
                "Authorization: $authHeader",
            ],
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);

        return $data['access_token'] ?? '';
    }
}
