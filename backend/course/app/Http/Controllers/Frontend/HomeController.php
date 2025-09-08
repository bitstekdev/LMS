<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BuilderPage;
use App\Models\Category;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Review;
use App\Models\User;
use App\Models\WatchDuration;
use App\Models\WatchHistory;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $pageBuilder = session('home')
            ? BuilderPage::find(session('home'))
            : BuilderPage::where('status', 1)->first();

        if ($pageBuilder && $pageBuilder->is_permanent) {
            return view('components.home_permanent_templates.'.$pageBuilder->identifier, [
                'blogs' => Blog::published()->latestPopular()->take(3)->get(),
                'reviews' => Review::all(),
            ]);
        }

        $instructors = User::with('courses')
            ->whereHas('courses')
            ->take(4)
            ->get();

        return view('frontend.'.get_frontend_settings('theme').'.home.index', [
            'instructor' => $instructors,
            'blogs' => Blog::published()->latestPopular()->take(3)->get(),
            'category' => Category::take(8)->get(),
        ]);
    }

    public function update_watch_history_with_duration(Request $request)
    {
        $user = auth('web')->user();
        $courseId = $request->input('course_id');
        $lessonId = $request->input('lesson_id');
        $currentDuration = $request->input('current_duration');

        $course = Course::findOrFail($courseId);
        $lesson = Lesson::findOrFail($lessonId);
        $dripSettings = json_decode($course->drip_content_settings ?? '{}', true);

        $watchDuration = WatchDuration::firstOrNew([
            'watched_course_id' => $courseId,
            'watched_lesson_id' => $lessonId,
            'watched_student_id' => $user->id,
        ]);

        $watchedCounter = json_decode($watchDuration->watched_counter, true) ?? [];

        if (! in_array($currentDuration, $watchedCounter)) {
            $watchedCounter[] = $currentDuration;
        }

        $watchDuration->watched_counter = json_encode($watchedCounter);
        $watchDuration->current_duration = $currentDuration;
        $watchDuration->save();

        if ($course->enable_drip_content !== 1) {
            return response()->json([
                'lesson_id' => $lessonId,
                'course_progress' => null,
                'is_completed' => null,
            ]);
        }

        $lessonSeconds = $lesson->duration_in_seconds; // Create accessor if needed
        $watchedSeconds = count($watchedCounter) * 5;
        $isCompleted = 0;

        if (($dripSettings['lesson_completion_role'] ?? '') === 'duration') {
            if ($watchedSeconds >= ($dripSettings['minimum_duration'] ?? 0) || ($watchedSeconds + 4) >= $lessonSeconds) {
                $isCompleted = 1;
            }
        } else {
            $required = ($lessonSeconds / 100) * ($dripSettings['minimum_percentage'] ?? 0);
            if ($watchedSeconds >= $required || ($watchedSeconds + 4) >= $lessonSeconds) {
                $isCompleted = 1;
            }
        }

        $courseProgress = 0;

        if ($isCompleted) {
            $history = WatchHistory::firstOrCreate([
                'course_id' => $courseId,
                'student_id' => $user->id,
            ]);

            $completedLessons = json_decode($history->completed_lesson, true) ?? [];

            if (! in_array($lessonId, $completedLessons)) {
                $completedLessons[] = $lessonId;

                $totalLessons = Lesson::where('course_id', $courseId)->count();
                $courseProgress = (100 / $totalLessons) * count($completedLessons);

                $history->completed_lesson = json_encode($completedLessons);
                $history->course_progress = $courseProgress;

                if ($courseProgress >= 100 && ! $history->completed_date) {
                    $history->completed_date = now();
                }

                $history->save();
            }
        }

        return response()->json([
            'lesson_id' => $lessonId,
            'course_progress' => round($courseProgress),
            'is_completed' => $isCompleted,
        ]);
    }

    public function homepage_switcher($id)
    {
        session(['home' => $id]);

        return redirect(route('home'));
    }
}
