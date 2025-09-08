<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Forum;
use App\Models\Lesson;
use App\Models\User;
use App\Models\WatchHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class PlayerController extends Controller
{
    public function course_player(Request $request, $slug, $id = '')
    {
        $course = Course::where('slug', $slug)->firstOrFail();

        // 🔐 Access Control for Paid Courses
        $user = auth('web')->user();
        if ($course->is_paid && $user->role !== 'admin') {
            if ($user->role === 'student') {
                $enroll_status = enroll_status($course->id, $user->id);

                if ($enroll_status === 'expired') {
                    Session::flash('error', get_phrase('Your course accessibility has expired. You need to buy it again'));

                    return redirect()->route('course.details', ['slug' => $slug]);
                } elseif (! $enroll_status) {
                    Session::flash('error', get_phrase('Not registered for this course.'));

                    return redirect()->route('course.details', ['slug' => $slug]);
                }
            }

            if ($user->role === 'instructor' && $course->user_id !== $user->id) {
                Session::flash('error', get_phrase('Not valid instructor.'));

                return redirect()->route('my.courses');
            }
        }

        // 🧠 Watch History Setup
        $watchHistory = WatchHistory::firstOrNew([
            'course_id' => $course->id,
            'student_id' => $user->id,
        ]);

        $first_lesson_id = Lesson::where('course_id', $course->id)->orderBy('sort')->value('id');

        if (empty($id)) {
            $id = $watchHistory->watching_lesson_id ?? $first_lesson_id;
        }

        if (! $watchHistory->exists && $id) {
            $watchHistory->watching_lesson_id = $id;
            $watchHistory->completed_lesson = json_encode([]);
            $watchHistory->save();
        }

        if ($id) {
            $watchHistory->update(['watching_lesson_id' => $id]);
        }

        // 🧾 Forum Questions with User Details
        $forumQuery = Forum::with(['user:id,name,photo'])
            ->where([
                ['parent_id', 0],
                ['course_id', $course->id],
            ])
            ->latest();

        if ($request->filled('search')) {
            $forumQuery->where(function ($query) use ($request) {
                $query->where('title', 'like', '%'.$request->search.'%')
                    ->orWhere('description', 'like', '%'.$request->search.'%');
            });
        }

        return view('course_player.index', [
            'course_details' => $course,
            'lesson_details' => Lesson::findOrNew($id),
            'history' => $watchHistory,
            'questions' => $forumQuery->get(),
        ]);
    }

    public function set_watch_history(Request $request)
    {
        $courseId = $request->course_id;
        $lessonId = $request->lesson_id;
        $userId = auth('web')->id();

        $course = Course::findOrFail($courseId);
        $enrollment = Enrollment::where('course_id', $courseId)->where('user_id', $userId)->first();

        if (! $enrollment && (auth('web')->user()->role !== 'admin' && ! is_course_instructor($courseId))) {
            Session::flash('error', get_phrase('Not registered for this course.'));

            return back();
        }

        $lessons = Lesson::where('course_id', $courseId)->pluck('id')->toArray();

        $watchHistory = WatchHistory::firstOrNew([
            'course_id' => $courseId,
            'student_id' => $userId,
        ]);

        $completed = is_array(json_decode($watchHistory->completed_lesson, true))
            ? json_decode($watchHistory->completed_lesson, true)
            : [];

        if (! in_array($lessonId, $completed)) {
            $completed[] = $lessonId;
        } else {
            $completed = array_values(array_diff($completed, [$lessonId]));
        }

        $watchHistory->completed_lesson = json_encode($completed);
        $watchHistory->watching_lesson_id = $lessonId;
        $watchHistory->completed_date = (count($lessons) === count($completed)) ? now() : null;
        $watchHistory->save();

        // 🧾 Award Certificate if completed
        if (progress_bar($courseId) >= 100) {
            Certificate::firstOrCreate(
                ['user_id' => $userId, 'course_id' => $courseId],
                ['identifier' => random(12), 'created_at' => now()]
            );
        }

        return back();
    }

    public function prepend_watermark()
    {
        return view('course_player.watermark');
    }
}
