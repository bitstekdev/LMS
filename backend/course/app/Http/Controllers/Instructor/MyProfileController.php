<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Services\FileUploaderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class MyProfileController extends Controller
{
    public function manage_profile()
    {
        return view('instructor.profile.index');
    }

    public function manage_profile_update(Request $request)
    {
        $user = auth('web')->user();

        if ($request->type === 'general') {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'facebook' => 'nullable|url',
                'twitter' => 'nullable|url',
                'linkedin' => 'nullable|url',
                'video_url' => 'nullable|url',
                'about' => 'nullable|string',
                'skills' => 'nullable|string',
                'biography' => 'nullable|string',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            $profile = $request->only([
                'name', 'email', 'facebook', 'twitter', 'linkedin',
                'video_url', 'about', 'skills', 'biography',
            ]);

            if ($request->hasFile('photo')) {
                $filename = 'uploads/users/admin/'.nice_file_name($request->name, $request->photo->extension());
                $profile['photo'] = $filename;
                app(FileUploaderService::class)->upload($request->photo, $filename, 400, null, 200, 200);
            }

            $user->update($profile);
        } else {
            $request->validate([
                'current_password' => 'required',
                'new_password' => 'required|string|min:6|confirmed',
            ]);

            if (! Hash::check($request->current_password, $user->password)) {
                return back()->with('error', get_phrase('Current password is incorrect.'));
            }

            $user->update(['password' => Hash::make($request->new_password)]);
        }

        return back()->with('success', get_phrase('Your changes have been saved.'));
    }

    public function manage_resume()
    {
        return view('instructor.resume.index');
    }

    public function education_add(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'institute' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
            'status' => 'nullable|in:ongoing,completed',
            'description' => 'nullable|string',
        ]);

        if ($validated['status'] === 'ongoing') {
            $validated['end_date'] = null;
        } else {
            $validated['status'] = 'completed';
        }

        $user = Auth::user();
        $educations = json_decode($user->educations, true) ?? [];

        $educations[] = $validated;
        $user->educations = json_encode($educations);
        $user->save();

        return redirect()->route('instructor.manage.resume')->with('success', 'Education added successfully.');
    }

    public function education_update(Request $request, $index)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'institute' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
            'status' => 'nullable|in:ongoing,completed',
            'description' => 'nullable|string',
        ]);

        if ($validated['status'] === 'ongoing') {
            $validated['end_date'] = null;
        } else {
            $validated['status'] = 'completed';
        }

        $user = Auth::user();
        $educations = json_decode($user->educations, true) ?? [];

        if (! isset($educations[$index])) {
            return redirect()->route('instructor.manage.resume')->with('error', 'Education entry not found.');
        }

        $educations[$index] = $validated;
        $user->educations = json_encode($educations);
        $user->save();

        return redirect()->route('instructor.manage.resume')->with('success', 'Education updated successfully.');
    }

    public function education_remove(Request $request, $index)
    {
        $user = Auth::user();
        $educations = json_decode($user->educations, true) ?? [];

        if (! isset($educations[$index])) {
            return redirect()->route('instructor.manage.resume')->with('error', 'Education entry not found.');
        }

        unset($educations[$index]);
        $user->educations = json_encode(array_values($educations)); // reindex
        $user->save();

        return redirect()->route('instructor.manage.resume')->with('success', 'Education deleted successfully.');
    }
}
