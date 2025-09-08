<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\BootcampLiveClass;
use App\Models\BootcampModule;
use App\Services\ZoomMeetingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BootcampLiveClassController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'module_id' => 'required|exists:bootcamp_modules,id',
            'description' => 'required|string',
            'status' => 'nullable|in:0,1',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $module = BootcampModule::find($request->module_id);
        if (! $module) {
            return back()->with('error', get_phrase('Module does not exist.'));
        }

        $start = strtotime("{$request->date} {$request->start_time}");
        $end = strtotime("{$request->date} {$request->end_time}");

        if (! $this->validateSchedule($module, $start, $end)) {
            return back()->with('error', get_phrase('Please set class schedule properly.'));
        }

        $duplicate = BootcampLiveClass::whereHas('module.bootcamp', function ($q) {
            $q->where('user_id', auth('web')->id());
        })
            ->where('module_id', $request->module_id)
            ->where('title', $request->title)
            ->exists();

        if ($duplicate) {
            return back()->with('error', get_phrase('This title has been taken.'));
        }

        $duration = ($end - $start) / 60;

        $zoomResponse = json_decode(ZoomMeetingService::createMeeting($request->title, $start, $duration), true);

        if (isset($zoomResponse['code'])) {
            return redirect()->route('instructor.bootcamp.edit', ['id' => $module->bootcamp_id, 'tab' => 'curriculum'])
                ->with('error', get_phrase($zoomResponse['message']));
        }

        BootcampLiveClass::create([
            'title' => $request->title,
            'slug' => slugify($request->title),
            'description' => $request->description,
            'status' => $request->status ?? 1,
            'module_id' => $request->module_id,
            'start_time' => $start,
            'end_time' => $end,
            'provider' => 'zoom',
            'joining_data' => json_encode($zoomResponse),
        ]);

        return redirect()->route('instructor.bootcamp.edit', ['id' => $module->bootcamp_id, 'tab' => 'curriculum'])
            ->with('success', get_phrase('Live class has been created.'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'module_id' => 'required|exists:bootcamp_modules,id',
            'description' => 'required|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $class = $this->getLiveClass($id, $request->module_id);
        if (! $class) {
            return back()->with('error', get_phrase('Data not found.'));
        }

        $start = strtotime("{$request->date} {$request->start_time}");
        $end = strtotime("{$request->date} {$request->end_time}");

        if (! $this->validateSchedule($class, $start, $end)) {
            return back()->with('error', get_phrase('Please set class schedule properly.'));
        }

        $duplicate = BootcampLiveClass::whereHas('module.bootcamp', function ($q) {
            $q->where('user_id', auth('web')->id());
        })
            ->where('module_id', $request->module_id)
            ->where('title', $request->title)
            ->where('id', '!=', $id)
            ->exists();

        if ($duplicate) {
            return back()->with('error', get_phrase('This title has been taken.'));
        }

        $data = [
            'title' => $request->title,
            'slug' => slugify($request->title),
            'description' => $request->description,
            'status' => $request->status ?? 1,
            'module_id' => $request->module_id,
            'start_time' => $start,
            'end_time' => $end,
        ];

        if ($class->start_time !== $start || $class->end_time !== $end) {
            $data['force_stop'] = 0;
        }

        if ($class->provider === 'zoom') {
            $oldData = json_decode($class->joining_data, true);
            ZoomMeetingService::updateMeeting($request->title, $request->start_time, $oldData['id']);

            $oldData['start_time'] = date('Y-m-d\TH:i:s', strtotime($request->start_time));
            $oldData['topic'] = $request->title;
            $data['joining_data'] = json_encode($oldData);
        }

        $class->update($data);

        return back()->with('success', get_phrase('Live class has been updated.'));
    }

    public function delete($id)
    {
        $class = $this->getLiveClass($id);
        if (! $class) {
            return back()->with('error', get_phrase('Data not found.'));
        }

        $meetingData = json_decode($class->joining_data, true);
        if (isset($meetingData['id'])) {
            ZoomMeetingService::deleteMeeting($meetingData['id']);
        }

        $class->delete();

        return back()->with('success', get_phrase('Live class has been deleted.'));
    }

    public function join_class($slug)
    {
        $class = BootcampLiveClass::with('module.bootcamp')
            ->where('slug', $slug)
            ->whereHas('module.bootcamp', fn ($q) => $q->where('user_id', auth('web')->id()))
            ->first();

        if (! $class) {
            return back()->with('error', get_phrase('Class not found.'));
        }

        if (get_settings('zoom_web_sdk') === 'active') {
            return view('bootcamp_online_class.index', [
                'class' => $class,
                'user' => get_user_info($class->module->bootcamp->user_id),
                'is_host' => 1,
            ]);
        }

        $meeting = json_decode($class->joining_data, true);

        return redirect($meeting['start_url'] ?? '/');
    }

    public function stop_class($id)
    {
        $class = $this->getLiveClass($id);
        if (! $class) {
            return back()->with('error', get_phrase('Data not found.'));
        }

        $class->update(['force_stop' => 1]);

        return back()->with('success', get_phrase('Class has been ended.'));
    }

    public function sort(Request $request)
    {
        $classes = json_decode($request->itemJSON);
        foreach ($classes as $index => $id) {
            BootcampLiveClass::where('id', $id)->update(['sort' => $index + 1]);
        }

        return response()->json([
            'status' => true,
            'success' => get_phrase('Classes sorted successfully'),
        ]);
    }

    // ──────────────────────────────
    // Utilities
    // ──────────────────────────────

    private function getLiveClass($id, $moduleId = null)
    {
        $query = BootcampLiveClass::with('module.bootcamp')
            ->where('id', $id)
            ->whereHas('module.bootcamp', fn ($q) => $q->where('user_id', auth('web')->id()));

        if ($moduleId) {
            $query->where('module_id', $moduleId);
        }

        return $query->first();
    }

    private function validateSchedule($module, $start, $end)
    {
        if (! $module->restriction) {
            return true;
        }

        if ($module->restriction == 1 && $start < $module->publish_date) {
            return false;
        }

        if ($module->restriction == 2) {
            return $start >= $module->publish_date && $end <= $module->expiry_date;
        }

        return true;
    }
}
