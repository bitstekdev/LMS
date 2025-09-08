<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BootcampLiveClass;
use App\Models\BootcampModule;
use App\Services\ZoomMeetingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class BootcampLiveClassController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'module_id' => 'required|exists:bootcamp_modules,id',
            'description' => 'required|string',
            'status' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $module = BootcampModule::find($request->module_id);
        if (! $module) {
            return back()->with('error', get_phrase('Module does not exist.'));
        }

        $start_timestamp = strtotime("{$request->date} {$request->start_time}");
        $end_timestamp = strtotime("{$request->date} {$request->end_time}");

        if ($module->restriction == 1 && $start_timestamp < $module->publish_date) {
            return back()->with('error', get_phrase('Class start time cannot be before module publish date.'));
        }

        if ($module->restriction == 2 &&
            ($start_timestamp < $module->publish_date || $end_timestamp > $module->expiry_date)) {
            return back()->with('error', get_phrase('Class time must be within module validity range.'));
        }

        $existing = BootcampLiveClass::join('bootcamp_modules', 'bootcamp_live_classes.module_id', '=', 'bootcamp_modules.id')
            ->join('bootcamps', 'bootcamp_modules.bootcamp_id', '=', 'bootcamps.id')
            ->where('bootcamp_live_classes.title', $request->title)
            ->where('bootcamp_modules.id', $request->module_id)
            ->where('bootcamps.user_id', auth('web')->id())
            ->first();

        if ($existing) {
            return back()->with('error', get_phrase('This title has already been used for this module.'));
        }

        $durationInMinutes = ($end_timestamp - $start_timestamp) / 60;

        $zoomResponse = ZoomMeetingService::createMeeting($request->title, $start_timestamp, $durationInMinutes);
        $zoomData = json_decode($zoomResponse, true);

        if (isset($zoomData['code'])) {
            return redirect()->route('admin.bootcamp.edit', ['id' => $module->bootcamp_id, 'tab' => 'curriculum'])
                ->with('error', get_phrase($zoomData['message']));
        }

        BootcampLiveClass::create([
            'title' => $request->title,
            'slug' => slugify($request->title),
            'description' => $request->description,
            'status' => $request->status,
            'module_id' => $request->module_id,
            'start_time' => $start_timestamp,
            'end_time' => $end_timestamp,
            'provider' => 'zoom',
            'joining_data' => $zoomResponse,
        ]);

        Session::flash('success', get_phrase('Live class has been created.'));

        return redirect()->route('admin.bootcamp.edit', ['id' => $module->bootcamp_id, 'tab' => 'curriculum']);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'module_id' => 'required|exists:bootcamp_modules,id',
            'description' => 'required|string',
            'status' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $class = BootcampLiveClass::with('module.bootcamp')
            ->where('id', $id)
            ->whereHas('module.bootcamp', fn ($q) => $q->where('user_id', auth('web')->id()))
            ->first();

        if (! $class) {
            return back()->with('error', get_phrase('Live class not found.'));
        }

        $start_timestamp = strtotime("{$request->date} {$request->start_time}");
        $end_timestamp = strtotime("{$request->date} {$request->end_time}");

        if ($class->restriction == 1 && $start_timestamp < $class->publish_date) {
            return back()->with('error', get_phrase('Start time is earlier than allowed.'));
        }

        if ($class->restriction == 2 &&
            ($start_timestamp < $class->publish_date || $end_timestamp > $class->expiry_date)) {
            return back()->with('error', get_phrase('Class time is outside the allowed module time.'));
        }

        $duplicate = BootcampLiveClass::join('bootcamp_modules', 'bootcamp_live_classes.module_id', '=', 'bootcamp_modules.id')
            ->join('bootcamps', 'bootcamp_modules.bootcamp_id', '=', 'bootcamps.id')
            ->where('bootcamp_live_classes.title', $request->title)
            ->where('bootcamp_modules.id', $request->module_id)
            ->where('bootcamp_live_classes.id', '!=', $id)
            ->where('bootcamps.user_id', auth('web')->id())
            ->first();

        if ($duplicate) {
            return back()->with('error', get_phrase('Another class with this title already exists in the module.'));
        }

        $data = [
            'title' => $request->title,
            'slug' => slugify($request->title),
            'description' => $request->description,
            'status' => $request->status,
            'module_id' => $request->module_id,
            'start_time' => $start_timestamp,
            'end_time' => $end_timestamp,
        ];

        if ($class->start_time !== $start_timestamp || $class->end_time !== $end_timestamp) {
            $data['force_stop'] = 0;
        }

        if ($class->provider === 'zoom') {
            $oldData = json_decode($class->joining_data, true);
            ZoomMeetingService::updateMeeting($request->title, $request->start_time, $oldData['id']);
            $oldData['start_time'] = date('Y-m-d\TH:i:s', $start_timestamp);
            $oldData['topic'] = $request->title;
            $data['joining_data'] = json_encode($oldData);
        }

        $class->update($data);

        Session::flash('success', get_phrase('Live class updated successfully.'));

        return back();
    }

    public function delete($id)
    {
        $class = BootcampLiveClass::with('module.bootcamp')
            ->where('id', $id)
            ->whereHas('module.bootcamp', fn ($q) => $q->where('user_id', auth('web')->id()))
            ->first();

        if (! $class) {
            return back()->with('error', get_phrase('Class not found.'));
        }

        if ($class->provider === 'zoom') {
            $meetingData = json_decode($class->joining_data, true);
            ZoomMeetingService::deleteMeeting($meetingData['id']);
        }

        $class->delete();

        Session::flash('success', get_phrase('Class has been deleted.'));

        return back();
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

        $zoomData = json_decode($class->joining_data, true);

        return redirect($zoomData['start_url']);
    }

    public function stop_class($id)
    {
        $class = BootcampLiveClass::with('module.bootcamp')
            ->where('id', $id)
            ->whereHas('module.bootcamp', fn ($q) => $q->where('user_id', auth('web')->id()))
            ->first();

        if (! $class) {
            return back()->with('error', get_phrase('Class not found.'));
        }

        $class->update(['force_stop' => 1]);

        Session::flash('success', get_phrase('Class has been ended.'));

        return back();
    }

    public function sort(Request $request)
    {
        $classes = json_decode($request->itemJSON, true);
        foreach ($classes as $index => $class_id) {
            BootcampLiveClass::where('id', $class_id)->update(['sort' => $index + 1]);
        }

        return response()->json([
            'status' => true,
            'success' => get_phrase('Classes sorted successfully.'),
        ]);
    }
}
