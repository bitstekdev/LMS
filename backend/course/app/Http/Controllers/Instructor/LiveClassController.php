<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\LiveClass;
use App\Models\Setting;
use Illuminate\Http\Request;

class LiveClassController extends Controller
{
    public function live_class_start($id)
    {
        $liveClass = LiveClass::findOrFail($id);

        if ($liveClass->provider === 'zoom') {
            if (get_settings('zoom_web_sdk') === 'active') {
                return view('course_player.live_class.zoom_live_class', compact('liveClass'));
            }

            $meetingInfo = json_decode($liveClass->additional_info, true);

            return redirect($meetingInfo['start_url'] ?? '/');
        }

        return view('course_player.live_class.zoom_live_class', compact('liveClass'));
    }

    public function live_class_store(Request $request, $courseId)
    {
        $request->validate([
            'class_topic' => 'required|max:255',
            'class_date_and_time' => 'required|date',
            'user_id' => 'required',
        ]);

        $data = [
            'class_topic' => $request->class_topic,
            'course_id' => $courseId,
            'user_id' => $request->user_id,
            'provider' => $request->provider,
            'class_date_and_time' => date('Y-m-d\TH:i:s', strtotime($request->class_date_and_time)),
            'note' => $request->note,
        ];

        if ($request->provider === 'zoom') {
            $meeting = json_decode($this->create_zoom_live_class($request->class_topic, $request->class_date_and_time), true);

            if (! empty($meeting['code'])) {
                return redirect()->route('instructor.course.edit', ['id' => $courseId, 'tab' => 'live-class'])
                    ->with('error', get_phrase($meeting['message']));
            }

            $data['additional_info'] = json_encode($meeting);
        }

        LiveClass::create($data);

        return redirect()->route('instructor.course.edit', ['id' => $courseId, 'tab' => 'live-class'])
            ->with('success', get_phrase('Live class added successfully'));
    }

    public function live_class_update(Request $request, $id)
    {
        $class = LiveClass::findOrFail($id);

        $request->validate([
            'class_topic' => 'required|max:255',
            'class_date_and_time' => 'required|date',
            'user_id' => 'required',
        ]);

        $data = [
            'class_topic' => $request->class_topic,
            'user_id' => $request->user_id,
            'class_date_and_time' => date('Y-m-d\TH:i:s', strtotime($request->class_date_and_time)),
            'note' => $request->note,
        ];

        if ($class->provider === 'zoom' && $class->additional_info) {
            $info = json_decode($class->additional_info, true);
            $this->update_zoom_live_class($request->class_topic, $request->class_date_and_time, $info['id']);
            $info['start_time'] = $data['class_date_and_time'];
            $info['topic'] = $request->class_topic;
            $data['additional_info'] = json_encode($info);
        }

        $class->update($data);

        return redirect()->route('instructor.course.edit', ['id' => $class->course_id, 'tab' => 'live-class'])
            ->with('success', get_phrase('Live class updated successfully'));
    }

    public function live_class_delete($id)
    {
        $class = LiveClass::findOrFail($id);
        $course = Course::findOrFail($class->course_id);

        if ($course->instructors()->exists()) {
            $info = json_decode($class->additional_info, true);
            if (! empty($info['id'])) {
                $this->delete_zoom_live_class($info['id']);
            }

            $class->delete();
        }

        return redirect()->route('instructor.course.edit', ['id' => $class->course_id, 'tab' => 'live-class'])
            ->with('success', get_phrase('Live class deleted successfully'));
    }

    public function live_class_settings()
    {
        return view('instructor.setting.live_class_settings');
    }

    public function update_live_class_settings(Request $request)
    {
        $request->validate([
            'zoom_account_email' => 'required|email',
            'zoom_web_sdk' => 'required|in:active,inactive',
            'zoom_account_id' => 'required',
            'zoom_client_id' => 'required',
            'zoom_client_secret' => 'required',
        ]);

        foreach ($request->all() as $type => $value) {
            Setting::updateOrCreate(['type' => $type], ['description' => $value]);
        }

        return redirect()->route('instructor.live.class.settings')
            ->with('success', get_phrase('Zoom live class settings have been configured'));
    }

    private function create_zoom_live_class($topic, $dateTime)
    {
        $token = $this->create_zoom_token();

        $payload = [
            'topic' => $topic,
            'schedule_for' => get_settings('zoom_account_email'),
            'type' => 2,
            'start_time' => date('Y-m-d\TH:i:s', strtotime($dateTime)),
            'duration' => 60,
            'timezone' => get_settings('timezone'),
            'settings' => [
                'approval_type' => 2,
                'join_before_host' => true,
                'jbh_time' => 0,
            ],
        ];

        return $this->zoom_request('https://api.zoom.us/v2/users/me/meetings', 'POST', $token, $payload);
    }

    private function update_zoom_live_class($topic, $dateTime, $meetingId)
    {
        $token = $this->create_zoom_token();

        $payload = [
            'topic' => $topic,
            'start_time' => date('Y-m-d\TH:i:s', strtotime($dateTime)),
        ];

        return $this->zoom_request("https://api.zoom.us/v2/meetings/{$meetingId}", 'PATCH', $token, $payload);
    }

    private function delete_zoom_live_class($meetingId)
    {
        $token = $this->create_zoom_token();

        return $this->zoom_request("https://api.zoom.us/v2/meetings/{$meetingId}", 'DELETE', $token);
    }

    private function create_zoom_token()
    {
        $clientId = get_settings('zoom_client_id');
        $clientSecret = get_settings('zoom_client_secret');
        $accountId = get_settings('zoom_account_id');

        $headers = [
            'Authorization: Basic '.base64_encode("{$clientId}:{$clientSecret}"),
            'Content-Type: application/x-www-form-urlencoded',
        ];

        $url = 'https://zoom.us/oauth/token?grant_type=account_credentials&account_id='.$accountId;

        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => $headers,
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        $data = json_decode($response, true);

        return $data['access_token'] ?? '';
    }

    private function zoom_request($url, $method, $token, $data = [])
    {
        $headers = [
            'Authorization: Bearer '.$token,
            'Content-Type: application/json',
        ];

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        if (! empty($data)) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }
}
