<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Wishlist;
use App\Services\FileUploaderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class UserController extends Controller
{
    public function update_userdata(Request $request)
    {
        try {
            $user = $request->user();

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'biography' => 'nullable|string',
                'about' => 'nullable|string',
                'address' => 'nullable|string',
                'facebook' => 'nullable|string',
                'twitter' => 'nullable|string',
                'linkedin' => 'nullable|string',
                'photo' => 'nullable|image|max:2048',
            ]);

            $userData = [
                'name' => htmlspecialchars($validated['name'], ENT_QUOTES, 'UTF-8'),
                'biography' => $validated['biography'] ?? null,
                'about' => $validated['about'] ?? null,
                'address' => $validated['address'] ?? null,
                'facebook' => htmlspecialchars($validated['facebook'] ?? '', ENT_QUOTES, 'UTF-8'),
                'twitter' => htmlspecialchars($validated['twitter'] ?? '', ENT_QUOTES, 'UTF-8'),
                'linkedin' => htmlspecialchars($validated['linkedin'] ?? '', ENT_QUOTES, 'UTF-8'),
            ];

            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                $fileName = Str::random(20).'.'.$file->getClientOriginalExtension();
                $path = "assets/upload/users/{$user->role}/{$fileName}";

                app(FileUploaderService::class)->upload($file, $path, null, null, 300);
                $userData['photo'] = $path;
            }

            $user->update($userData);
            $user->refresh();
            $user->photo = url('public/'.$user->photo);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => $user,
                'errors' => null,
            ]);
        } catch (Throwable $e) {
            Log::error('Update user data error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile.',
                'data' => null,
                'errors' => null,
            ], 500);
        }
    }

    public function update_password(Request $request)
    {
        try {
            $request->validate([
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8',
                'confirm_password' => 'required|same:new_password',
            ]);

            $user = $request->user();

            if (! Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is invalid.',
                    'data' => null,
                    'errors' => null,
                ], 400);
            }

            if ($request->current_password === $request->new_password) {
                return response()->json([
                    'success' => false,
                    'message' => 'New password cannot be the same as the current password.',
                    'data' => null,
                    'errors' => null,
                ], 409);
            }

            $user->update(['password' => Hash::make($request->new_password)]);

            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully.',
                'data' => null,
                'errors' => null,
            ]);
        } catch (Throwable $e) {
            Log::error('Update password error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to change password.',
                'data' => null,
                'errors' => null,
            ], 500);
        }
    }

    public function account_disable(Request $request)
    {
        try {
            $request->validate([
                'account_password' => 'required|string',
            ]);

            $user = $request->user();

            if (! Hash::check($request->account_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password mismatch.',
                    'data' => null,
                    'errors' => null,
                ], 400);
            }

            $user->update(['status' => 0]);

            return response()->json([
                'success' => true,
                'message' => 'Account has been disabled.',
                'data' => null,
                'errors' => null,
            ]);
        } catch (Throwable $e) {
            Log::error('Account disable error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to disable account.',
                'data' => null,
                'errors' => null,
            ], 500);
        }
    }

    public function my_wishlist(Request $request)
    {
        $user = $request->user();
        $wishlist = Wishlist::where('user_id', $user->id)->pluck('course_id');

        if (count($wishlist) > 0) {
            $courses = Course::whereIn('id', $wishlist)->get();
            $response = course_data($courses);
        } else {
            $response = [];
        }

        return $response;
    }

    public function toggle_wishlist_items(Request $request)
    {
        $user = $request->user();
        $course_id = $request->course_id;

        $exists = Wishlist::where('user_id', $user->id)->where('course_id', $course_id)->first();
        if ($exists) {
            $exists->delete();

            return ['status' => 'removed'];
        } else {
            Wishlist::create(['user_id' => $user->id, 'course_id' => $course_id]);

            return ['status' => 'added'];
        }
    }

    public function my_courses(Request $request)
    {
        $user = $request->user();
        $enrollments = Enrollment::with('course')->where('user_id', $user->id)->orderByDesc('id')->get();

        $courses = [];
        foreach ($enrollments as $enrollment) {
            if ($enrollment->course) {
                $courseData = course_data([$enrollment->course])[0] ?? [];
                $courseData['completion'] = round(course_progress($enrollment->course->id, $user->id));
                $courseData['total_number_of_lessons'] = count(get_lessons('course', $enrollment->course->id));
                $courseData['total_number_of_completed_lessons'] = get_completed_number_of_lesson($user->id, 'course', $enrollment->course->id);
                $courses[] = $courseData;
            }
        }

        return $courses;
    }

    public function save_course_progress(Request $request)
    {
        $request->validate(['lesson_id' => 'required|integer']);
        $user = $request->user();

        $lessons = get_lessons('lesson', $request->lesson_id);
        if (empty($lessons)) {
            return response()->json(['success' => false, 'message' => 'Lesson not found'], 404);
        }

        update_watch_history_manually($request->lesson_id, $lessons[0]->course_id, $user->id);

        return course_completion_data($lessons[0]->course_id, $user->id);
    }
}
