<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\BootcampModule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BootcampModuleController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'validity' => 'required',
            'bootcamp_id' => 'required|exists:bootcamps,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Check duplicate title for the same bootcamp and user
        $exists = BootcampModule::join('bootcamps', 'bootcamp_modules.bootcamp_id', '=', 'bootcamps.id')
            ->where('bootcamp_modules.title', $request->title)
            ->where('bootcamp_modules.bootcamp_id', $request->bootcamp_id)
            ->where('bootcamps.user_id', auth('web')->id())
            ->exists();

        if ($exists) {
            return back()->with('error', get_phrase('This title has been taken.'));
        }

        [$startDate, $endDate] = explode('-', $request->validity);

        BootcampModule::create([
            'title' => $request->title,
            'restriction' => $request->restriction,
            'bootcamp_id' => $request->bootcamp_id,
            'publish_date' => strtotime($startDate),
            'expiry_date' => strtotime($endDate),
        ]);

        return back()->with('success', get_phrase('Module has been created.'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'validity' => 'required',
            'bootcamp_id' => 'required|exists:bootcamps,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $module = BootcampModule::join('bootcamps', 'bootcamp_modules.bootcamp_id', '=', 'bootcamps.id')
            ->where('bootcamp_modules.id', $id)
            ->where('bootcamp_modules.bootcamp_id', $request->bootcamp_id)
            ->where('bootcamps.user_id', auth('web')->id())
            ->select('bootcamp_modules.*')
            ->first();

        if (! $module) {
            return back()->with('error', get_phrase('Data not found.'));
        }

        // Check duplicate title (excluding current module)
        $duplicate = BootcampModule::join('bootcamps', 'bootcamp_modules.bootcamp_id', '=', 'bootcamps.id')
            ->where('bootcamp_modules.id', '!=', $id)
            ->where('bootcamp_modules.title', $request->title)
            ->where('bootcamp_modules.bootcamp_id', $request->bootcamp_id)
            ->where('bootcamps.user_id', auth('web')->id())
            ->exists();

        if ($duplicate) {
            return back()->with('error', get_phrase('This title has been taken.'));
        }

        [$startDate, $endDate] = explode('-', $request->validity);

        $module->update([
            'title' => $request->title,
            'restriction' => $request->restriction,
            'publish_date' => strtotime($startDate),
            'expiry_date' => strtotime($endDate),
        ]);

        return back()->with('success', get_phrase('Module has been updated.'));
    }

    public function delete($id)
    {
        $module = BootcampModule::join('bootcamps', 'bootcamp_modules.bootcamp_id', '=', 'bootcamps.id')
            ->where('bootcamp_modules.id', $id)
            ->where('bootcamps.user_id', auth('web')->id())
            ->select('bootcamp_modules.id')
            ->first();

        if (! $module) {
            return back()->with('error', get_phrase('Data not found.'));
        }

        BootcampModule::destroy($module->id);

        return back()->with('success', get_phrase('Module has been deleted.'));
    }

    public function sort(Request $request)
    {
        $modules = json_decode($request->itemJSON);

        foreach ($modules as $index => $id) {
            BootcampModule::where('id', $id)->update(['sort' => $index + 1]);
        }

        return response()->json([
            'status' => true,
            'success' => get_phrase('Modules sorted successfully'),
        ]);
    }
}
