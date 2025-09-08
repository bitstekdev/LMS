<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BootcampModule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class BootcampModuleController extends Controller
{
    public function store(Request $request)
    {
        $rules = [
            'title' => 'required|string',
            'validity' => 'required|string',
            'restriction' => 'nullable|in:free,paid', // optional but restricted
            'bootcamp_id' => 'required|integer',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Check for duplicate title in the same bootcamp
        $existing = BootcampModule::join('bootcamps', 'bootcamp_modules.bootcamp_id', '=', 'bootcamps.id')
            ->where('bootcamp_modules.title', $request->title)
            ->where('bootcamp_modules.bootcamp_id', $request->bootcamp_id)
            ->where('bootcamps.user_id', auth('web')->user()->id)
            ->first();

        if ($existing) {
            Session::flash('error', get_phrase('This title has already been used for this bootcamp.'));

            return back();
        }

        [$start, $end] = array_map('trim', explode('-', $request->validity));

        $data = [
            'title' => $request->title,
            'restriction' => $request->restriction,
            'bootcamp_id' => $request->bootcamp_id,
            'publish_date' => strtotime($start),
            'expiry_date' => strtotime($end),
        ];

        BootcampModule::insert($data);

        Session::flash('success', get_phrase('Module has been created.'));

        return back();
    }

    public function update(Request $request, $id)
    {
        $module = BootcampModule::where('id', $id)->where('bootcamp_id', $request->bootcamp_id);

        if (! $module->exists()) {
            Session::flash('error', get_phrase('Module not found.'));

            return back();
        }

        $rules = [
            'title' => 'required|string',
            'validity' => 'required|string',
            'restriction' => 'nullable|in:free,paid',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Check for duplicate title in the same bootcamp (excluding this one)
        $existing = BootcampModule::join('bootcamps', 'bootcamp_modules.bootcamp_id', '=', 'bootcamps.id')
            ->where('bootcamp_modules.title', $request->title)
            ->where('bootcamp_modules.bootcamp_id', $request->bootcamp_id)
            ->where('bootcamp_modules.id', '!=', $id)
            ->where('bootcamps.user_id', auth('web')->user()->id)
            ->first();

        if ($existing) {
            Session::flash('error', get_phrase('This title has already been used for this bootcamp.'));

            return back();
        }

        [$start, $end] = array_map('trim', explode('-', $request->validity));

        $data = [
            'title' => $request->title,
            'restriction' => $request->restriction,
            'publish_date' => strtotime($start),
            'expiry_date' => strtotime($end),
        ];

        $module->update($data);

        Session::flash('success', get_phrase('Module has been updated.'));

        return back();
    }

    public function delete($id)
    {
        $module = BootcampModule::where('id', $id);

        if (! $module->exists()) {
            Session::flash('error', get_phrase('Module not found.'));

            return back();
        }

        $module->delete();

        Session::flash('success', get_phrase('Module has been deleted.'));

        return back();
    }

    public function sort(Request $request)
    {
        $modules = json_decode($request->itemJSON, true);

        if (is_array($modules)) {
            foreach ($modules as $index => $id) {
                BootcampModule::where('id', $id)->update(['sort' => $index + 1]);
            }
        }

        return response()->json([
            'status' => true,
            'success' => get_phrase('Modules sorted successfully.'),
        ]);
    }
}
