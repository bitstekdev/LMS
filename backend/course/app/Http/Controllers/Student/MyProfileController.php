<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\FileUploaderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MyProfileController extends Controller
{
    public function index()
    {
        $user = User::findOrFail(Auth::id());
        $theme = get_frontend_settings('theme');

        return view("frontend.{$theme}.student.my_profile.index", [
            'user_details' => $user,
        ]);
    }

    public function update(Request $request, $user_id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => "required|email|unique:users,email,{$user_id}",
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        User::where('id', $user_id)->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'website' => $request->website,
            'facebook' => $request->facebook,
            'twitter' => $request->twitter,
            'linkedin' => $request->linkedin,
            'skills' => $request->skills,
            'biography' => $request->biography,
        ]);

        Session::flash('success', get_phrase('Profile updated successfully.'));

        return back();
    }

    public function update_profile_picture(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,webp,tiff|max:3072',
        ]);

        $file = $request->file('photo');
        $file_name = Str::random(20).'.'.$file->getClientOriginalExtension();
        $path = 'uploads/users/'.Auth::user()->role.'/'.$file_name;

        app(FileUploaderService::class)->upload($file, $path, null, null, 300);

        Auth::user()->update(['photo' => $path]);

        Session::flash('success', get_phrase('Profile picture updated.'));

        return back();
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:4',
            'confirm_password' => 'required|same:new_password',
        ]);

        if (! Hash::check($request->current_password, Auth::user()->password)) {
            return back()->with('error', 'Current password is incorrect.');
        }

        Auth::user()->update(['password' => Hash::make($request->new_password)]);

        Session::flash('success', 'Password changed successfully.');

        return back();
    }
}
