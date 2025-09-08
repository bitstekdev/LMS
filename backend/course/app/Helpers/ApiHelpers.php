<?php

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Review;
use App\Models\Section;
use App\Models\WatchHistory;
use App\Models\Wishlist;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Return enrollments for a course.
 *
 * When $distinctData=true, returns a distinct list of user_ids who enrolled
 * the given course. Otherwise returns all Enrollment models (optionally filtered by course).
 *
 * @param  int|string  $courseId
 * @param  bool  $distinctData
 * @return \Illuminate\Support\Collection
 */
if (! function_exists('enroll_history')) {
    function enroll_history($courseId = '', bool $distinctData = false)
    {
        $query = Enrollment::query();

        if (! empty($courseId)) {
            $query->where('course_id', $courseId);
        }

        if ($distinctData) {
            return $query->select('user_id')->distinct()->get();
        }

        return $query->get();
    }
}

/**
 * Hydrate / normalize course data for API responses.
 *
 * - Decodes JSON fields (requirements, outcomes, faqs, instructor_ids)
 * - Resolves media URLs (thumbnail, banner, preview)
 * - Formats price (Free / discounted / normal)
 * - Attaches instructor info, total_enrollment, shareable_link
 * - Calculates ratings and average_rating
 *
 * @param  iterable|\Illuminate\Support\Collection  $courses
 * @return \Illuminate\Support\Collection
 */
if (! function_exists('course_data')) {
    function course_data($courses)
    {
        $courses = collect($courses);

        return $courses->map(function ($course) {
            /** @var \App\Models\Course $course */

            // Already casted as arrays
            $course->requirements = $course->requirements ?? [];
            $course->outcomes = $course->outcomes ?? [];
            $course->faqs = $course->faqs ?? [];
            $course->instructors = $course->instructor_ids ?? [];

            // Media
            $course->thumbnail = get_photo('course_thumbnail', $course->thumbnail);
            $course->banner = get_photo('course_banner', $course->banner);

            // Preview URL normalization
            $preview = (string) ($course->preview ?? '');
            if (
                Str::contains($preview, ['youtube.com', 'youtu.be', 'vimeo.com', 'drive.google.com']) ||
                (Str::contains($preview, '.mp4') && Str::contains($preview, 'http'))
            ) {
                $course->preview = $preview;
            } else {
                $course->preview = $preview ? url('public/'.ltrim($preview, '/')) : null;
            }

            // Price / labels
            if ((int) $course->is_paid === 0) {
                $course->price = 'Free';
                $course->price_cart = 0;
            } elseif ((int) $course->discount_flag === 1 && ! is_null($course->discounted_price)) {
                $course->price = currency($course->discounted_price);
                $course->price_cart = $course->discounted_price;
            } else {
                $course->price_cart = $course->price;
                $course->price = currency($course->price);
            }

            // Instructor info
            $instructor = get_user_info($course->user_id);
            $course->user->name = $instructor->name ?? '';
            $course->instructor_image = $instructor->photo ? url('public/'.ltrim($instructor->photo, '/')) : null;

            // Enrollment stats
            $course->total_enrollment = enroll_history($course->id)->count();

            // Shareable link
            $course->shareable_link = url('course/'.slugify($course->title));

            // Reviews & rating
            $reviews = Review::where('course_id', $course->id)->get();
            $total = $reviews->count();
            $sum = (int) $reviews->sum('rating');
            $avg = $total ? round($sum / $total, 1) : 0.0;

            $course->total_reviews = $total;
            $course->average_rating = number_format($avg, 1, '.', '');

            return $course;
        });
    }
}

/**
 * Resolve a public URL for images with graceful fallbacks by type.
 *
 * @param  string  $type  e.g. 'user_image', 'course_thumbnail', 'course_banner', 'course_preview', 'category_thumbnail'
 * @param  string  $identifier  relative path stored in DB
 * @return string absolute URL
 */
if (! function_exists('get_photo')) {
    function get_photo(string $type, ?string $identifier)
    {
        $identifier = $identifier ? ltrim($identifier, '/') : '';
        $publicPath = $identifier ? public_path($identifier) : null;

        $map = [
            'user_image' => 'assets/upload/users/student/placeholder/placeholder.png',
            'course_thumbnail' => 'uploads/course-thumbnail/placeholder/placeholder.png',
            'course_banner' => 'uploads/course-banner/placeholder/placeholder.png',
            'course_preview' => 'uploads/course-preview/placeholder/placeholder.png',
            'category_thumbnail' => 'uploads/category-thumbnail/placeholder/placeholder.png',
        ];

        $fallback = $map[$type] ?? $map['course_thumbnail'];

        if ($publicPath && File::exists($publicPath) && $identifier !== '') {
            return url('public/'.$identifier);
        }

        return url('public/'.$fallback);
    }
}

/**
 * Get active courses for a given category.
 *
 * @param  int|string  $categoryId
 * @return \Illuminate\Support\Collection|\App\Models\Course[]
 */
if (! function_exists('get_category_wise_courses')) {
    function get_category_wise_courses($categoryId = '')
    {
        return Course::query()
            ->where('category_id', $categoryId)
            ->where('status', 'active')
            ->get();
    }
}

/**
 * Get category details by its ID (returns a collection for backward compatibility).
 *
 * @param  int|string  $id
 * @return \Illuminate\Support\Collection
 */
if (! function_exists('get_category_details_by_id')) {
    function get_category_details_by_id($id)
    {
        return Category::query()->whereKey($id)->get();
    }
}

/**
 * Return list of subcategories for a parent category, enriched with:
 * - number_of_courses (active courses count)
 * - thumbnail (resolved URL)
 *
 * @param  int  $parentCategoryId
 * @return array
 */
if (! function_exists('sub_categories')) {
    function sub_categories($parentCategoryId)
    {
        $response = [];

        $categories = Category::query()
            ->where('parent_id', $parentCategoryId)
            ->get();

        foreach ($categories as $category) {
            $category->number_of_courses = Course::query()
                ->where('status', 'active')
                ->where('category_id', $category->id)
                ->count();

            $category->thumbnail = get_photo('category_thumbnail', $category->thumbnail);
            $response[] = $category;
        }

        return $response;
    }
}

/**
 * Get course model(s) by id as a Collection of Eloquent models (1 item).
 *
 * @param  int|string  $courseId
 * @return \Illuminate\Support\Collection<\App\Models\Course>
 */
function get_course_by_id($courseId = ''): Collection
{
    if ($courseId === '') {
        return collect();
    }

    // Load sections (ordered) and each section’s lessons (ordered)
    $course = Course::with([
        'sections' => fn ($q) => $q->orderBy('sort'),
        'sections.lessons' => fn ($q) => $q->orderBy('sort'),
    ])->whereKey($courseId)->first();

    return $course ? collect([$course]) : collect();
}

/**
 * Return normalized course details with flags and computed includes.
 *
 * Avoids dynamic properties:
 * - we set loaded relation via setRelation(...)
 * - we add computed fields via setAttribute(...)
 *
 * @param  int|string  $userId
 * @param  int|string  $courseId
 * @return \Illuminate\Support\Collection
 */
if (! function_exists('course_details_by_id')) {
    function course_details_by_id($userId = '', $courseId = '')
    {
        $courses = get_course_by_id($courseId);

        return $courses->map(function (Course $course) use ($userId) {

            // sections already eager-loaded from get_course_by_id()
            // (If you want to override with a custom builder, use setRelation)
            // $course->setRelation('sections', sections($course->id));

            // Flags & computed attributes: use setAttribute to avoid dynamic props
            $course->setAttribute('is_wishlisted', is_added_to_wishlist($userId, $course->id));
            $course->setAttribute('is_purchased', is_purchased($userId, $course->id));
            $course->setAttribute('is_cart', is_cart_item($userId, $course->id));

            $includes = [
                get_total_duration_of_lesson_by_course_id($course->id).' On demand videos',
                get_lessons('course', $course->id)->count().' Lessons',
                'High quality videos',
                'Life time access',
            ];
            $course->setAttribute('includes', $includes);

            return $course;
        });
    }
}

/**
 * Get course (collection for compatibility).
 *
 * @param  int|string  $courseId
 * @return \Illuminate\Support\Collection|\App\Models\Course[]
 */
if (! function_exists('get_course_by_id')) {
    function get_course_by_id($courseId = '')
    {
        return Course::query()->whereKey($courseId)->get();
    }
}

/**
 * Check if a course is wishlisted by a user.
 *
 * @param  int|string  $userId
 * @param  int|string  $courseId
 * @return bool
 */
if (! function_exists('is_added_to_wishlist')) {
    function is_added_to_wishlist($userId = 0, $courseId = '')
    {
        if ((int) $userId <= 0) {
            return false;
        }

        return Wishlist::query()
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->exists();
    }
}

/**
 * Check if a user has a valid enrollment for a course.
 *
 * @param  int|string  $userId
 * @param  int|string  $courseId
 * @return bool
 */
if (! function_exists('is_purchased')) {
    function is_purchased($userId = 0, $courseId = '')
    {
        if ((int) $userId <= 0) {
            return false;
        }

        return enroll_status_api($courseId, $userId) === true;
    }
}

/**
 * Enrollment status check (true if enrollment exists).
 *
 * @param  int|string  $courseId
 * @param  int|string  $userId
 * @return bool
 */
if (! function_exists('enroll_status_api')) {
    function enroll_status_api($courseId = '', $userId = '')
    {
        return Enrollment::query()
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->exists();
    }
}

/**
 * Get total duration (HH:MM:SS) of all video-like lessons in a course.
 *
 * @param  int|string  $courseId
 * @return string "HH:MM:SS hours"
 */
if (! function_exists('get_total_duration_of_lesson_by_course_id')) {
    function get_total_duration_of_lesson_by_course_id($courseId)
    {
        $total = 0;

        $lessons = get_lessons('course', $courseId);
        foreach ($lessons as $lesson) {
            if (! in_array($lesson->lesson_type, ['other', 'text'], true)) {
                $hms = $lesson->duration ?: '00:00:00';
                [$h, $m, $s] = array_pad(explode(':', $hms), 3, 0);
                $total += ((int) $h * 3600) + ((int) $m * 60) + (int) $s;
            }
        }

        $hours = floor($total / 3600);
        $minutes = floor(($total % 3600) / 60);
        $seconds = $total % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds).' '.get_phrase('hours');
    }
}

/**
 * Calculate a user's course progress percentage (0..100).
 * If $returnType === 'completed_lesson_ids', returns the list of completed lesson IDs.
 *
 * @param  int|string  $courseId
 * @param  int|string  $userId
 * @param  string  $returnType
 * @return int|array
 */
if (! function_exists('course_progress')) {
    function course_progress($courseId = '', $userId = '', $returnType = '')
    {
        $watch = WatchHistory::query()
            ->where('student_id', $userId)
            ->where('course_id', $courseId)
            ->first();

        $totalLessons = Lesson::query()
            ->where('course_id', $courseId)
            ->count();

        $completedIds = [];
        $progress = 0;

        if ($watch) {
            $completedIds = json_decode($watch->completed_lesson ?? '[]', true) ?: [];
            $progress = ($totalLessons > 0)
                ? (count($completedIds) / $totalLessons) * 100
                : 0;
        }

        if ($returnType === 'completed_lesson_ids') {
            return $completedIds;
        }

        return $progress > 0 ? $progress : 0;
    }
}

/**
 * Determine if a lesson is completed by the user (1) or not (0).
 * If $courseId is omitted, it's inferred from the lesson.
 *
 * @param  int|string  $lessonId
 * @param  int|string  $userId
 * @param  int|string  $courseId
 * @return int
 */
if (! function_exists('lesson_progress_api')) {
    function lesson_progress_api($lessonId = '', $userId = '', $courseId = '')
    {
        if (empty($courseId)) {
            $courseId = Lesson::query()->whereKey($lessonId)->value('course_id');
        }

        $history = WatchHistory::query()
            ->where('student_id', $userId)
            ->where('course_id', $courseId)
            ->first();

        if (! $history) {
            return 0;
        }

        $ids = json_decode($history->completed_lesson ?? '[]', true) ?: [];

        return is_array($ids) && in_array($lessonId, $ids, true) ? 1 : 0;
    }
}

/**
 * Get lessons ordered by sort for a course/section/specific lesson/all.
 *
 * @param  'course'|'section'|'lesson'|''  $type
 * @param  int|string  $id
 * @return \Illuminate\Support\Collection|\App\Models\Lesson[]
 */
if (! function_exists('get_lessons')) {
    function get_lessons($type = '', $id = '')
    {
        return match ($type) {
            'course' => Lesson::query()->where('course_id', $id)->orderBy('sort')->get(),
            'section' => Lesson::query()->where('section_id', $id)->orderBy('sort')->get(),
            'lesson' => Lesson::query()->whereKey($id)->orderBy('sort')->get(),
            default => Lesson::query()->orderBy('sort')->get(),
        };
    }
}

/**
 * Toggle a lesson as completed for a user in a course, and return progress payload.
 *
 * Returns JSON: { lesson_id, course_progress, is_completed }
 *
 * @param  int|string  $lessonId
 * @param  int|string  $courseId
 * @param  int|string  $userId
 * @return string JSON
 */
if (! function_exists('update_watch_history_manually')) {
    function update_watch_history_manually($lessonId = '', $courseId = '', $userId = '')
    {
        $isCompleted = 0;

        $watch = WatchHistory::query()
            ->where('course_id', $courseId)
            ->where('student_id', $userId)
            ->first();

        $completed = $watch ? json_decode($watch->completed_lesson ?? '[]', true) : [];
        if (! is_array($completed)) {
            $completed = [];
        }

        // Toggle
        if (! in_array($lessonId, $completed, true)) {
            $completed[] = $lessonId;
            $isCompleted = 1;
        } else {
            $completed = array_values(array_diff($completed, [$lessonId]));
            $isCompleted = 0;
        }

        // Recompute progress + completed_date
        $totalLessons = Lesson::query()->where('course_id', $courseId)->count();
        $courseProgress = $totalLessons > 0 ? (100 / $totalLessons) * count($completed) : 0;

        $completedDate = $watch?->completed_date;
        if ($courseProgress >= 100 && empty($completedDate)) {
            // original code used a unix timestamp string; keep that shape
            $completedDate = time();
        }

        // Persist
        if ($watch) {
            $watch->update([
                'completed_lesson' => json_encode(array_values($completed)),
                'watching_lesson_id' => $lessonId,
                'completed_date' => $completedDate,
            ]);
        } else {
            WatchHistory::create([
                'course_id' => $courseId,
                'student_id' => $userId,
                'completed_lesson' => json_encode([$lessonId]),
                'watching_lesson_id' => $lessonId,
                'completed_date' => null,
            ]);
        }

        return json_encode([
            'lesson_id' => $lessonId,
            'course_progress' => (int) round($courseProgress),
            'is_completed' => $isCompleted,
        ]);
    }
}

/**
 * Build course completion summary for a user.
 *
 * @param  int|string  $courseId
 * @param  int|string  $userId
 * @return array{course_id:int|string, number_of_lessons:int, number_of_completed_lessons:int, course_progress:int}
 */
if (! function_exists('course_completion_data')) {
    function course_completion_data($courseId = '', $userId = '')
    {
        return [
            'course_id' => $courseId,
            'number_of_lessons' => get_lessons('course', $courseId)->count(),
            'number_of_completed_lessons' => get_completed_number_of_lesson($userId, 'course', $courseId),
            'course_progress' => (int) round(course_progress($courseId, $userId)),
        ];
    }
}

/**
 * Count how many lessons are completed by a user within a course or a section.
 *
 * @param  int|string  $userId
 * @param  'section'|'course'  $type
 * @param  int|string  $id
 * @return int
 */
if (! function_exists('get_completed_number_of_lesson')) {
    function get_completed_number_of_lesson($userId = '', $type = '', $id = '')
    {
        $lessons = $type === 'section'
            ? get_lessons('section', $id)
            : get_lessons('course', $id);

        $count = 0;
        foreach ($lessons as $lesson) {
            $count += lesson_progress_api($lesson->id, $userId) ? 1 : 0;
        }

        return $count;
    }
}

/**
 * Return section list + lessons for a course, enriched with:
 * - total_duration
 * - lesson counters (start/end)
 * - completed_lesson_number (if $userId provided)
 * - user_validity flag (true)
 *
 * @param  int|string  $courseId
 * @param  int|string  $userId
 * @return array
 */
if (! function_exists('sections')) {
    function sections($courseId = '', $userId = '')
    {
        $list = api_get_section('course', $courseId);
        $lessonStart = 0;
        $lessonEnd = 0;

        foreach ($list as $idx => $section) {
            $lessons = section_wise_lessons($section->id, $userId);

            $list[$idx]->lessons = $lessons;
            $list[$idx]->total_duration = str_replace(' Hours', '', get_total_duration_of_lesson_by_section_id($section->id));

            if ($idx === 0) {
                $lessonStart = 1;
                $lessonEnd = count($lessons);
            } else {
                $lessonStart = $lessonEnd + 1;
                $lessonEnd = $lessonStart + count($lessons);
            }

            $list[$idx]->lesson_counter_starts = $lessonStart;
            $list[$idx]->lesson_counter_ends = $lessonEnd;
            $list[$idx]->completed_lesson_number = $userId ? get_completed_number_of_lesson($userId, 'section', $section->id) : 0;
        }

        return add_user_validity($list);
    }
}

/**
 * Get sections by course or specific section id (ordered by "sort").
 *
 * @param  'course'|'section'  $typeBy
 * @param  int|string  $id
 * @return \Illuminate\Support\Collection|\App\Models\Section[]
 */
if (! function_exists('api_get_section')) {
    function api_get_section($typeBy, $id)
    {
        return match ($typeBy) {
            'course' => Section::query()->where('course_id', $id)->orderBy('sort')->get(),
            'section' => Section::query()->whereKey($id)->orderBy('sort')->get(),
            default => collect(),
        };
    }
}

/**
 * Build a section's lesson payload for API responses.
 *
 * @param  int|string  $sectionId
 * @param  int|string  $userId
 * @return array
 */
if (! function_exists('section_wise_lessons')) {
    function section_wise_lessons($sectionId = '', $userId = '')
    {
        $response = [];

        $lessons = get_lessons('section', $sectionId);
        foreach ($lessons as $lesson) {
            $row = [
                'id' => $lesson->id,
                'title' => $lesson->title,
                'duration' => readable_time_for_humans($lesson->duration),
                'course_id' => $lesson->course_id,
                'section_id' => $lesson->section_id,
                'video_type' => $lesson->video_type ?: '',
                'lesson_type' => $lesson->lesson_type,
                'is_free' => (int) $lesson->is_free,
                'attachment' => $lesson->lesson_type === 'text'
                    ? remove_js(html_entity_decode((string) $lesson->attachment))
                    : $lesson->attachment,
                'attachment_url' => $lesson->attachment
                    ? url('/public/uploads/lesson_file/attachment/'.ltrim($lesson->attachment, '/'))
                    : null,
                'attachment_type' => $lesson->attachment_type,
                'summary' => remove_js(html_entity_decode((string) $lesson->summary)),
                'is_completed' => $userId ? lesson_progress_api($lesson->id, $userId) : 0,
                'user_validity' => true,
            ];

            if ($lesson->lesson_type === 'system-video') {
                $row['video_url'] = $lesson->lesson_src
                    ? url('/public/assets/upload/lesson_file/videos/'.ltrim($lesson->lesson_src, '/'))
                    : '';
            } else {
                $row['video_url'] = $lesson->lesson_src ?: '';
            }

            $response[] = $row;
        }

        return $response;
    }
}

/**
 * Add a 'user_validity' => true flag to each response item.
 *
 * @param  array|\Illuminate\Support\Collection  $responses
 * @return array|\Illuminate\Support\Collection
 */
if (! function_exists('add_user_validity')) {
    function add_user_validity($responses = [])
    {
        foreach ($responses as $k => $resp) {
            $responses[$k]->user_validity = true;
        }

        return $responses;
    }
}

/**
 * Get total duration (HH:MM:SS) of all video-like lessons in a section.
 *
 * @param  int|string  $sectionId
 * @return string
 */
if (! function_exists('get_total_duration_of_lesson_by_section_id')) {
    function get_total_duration_of_lesson_by_section_id($sectionId)
    {
        $total = 0;

        $lessons = get_lessons('section', $sectionId);
        foreach ($lessons as $lesson) {
            if (! in_array($lesson->lesson_type, ['other', 'text'], true)) {
                $hms = $lesson->duration ?: '00:00:00';
                [$h, $m, $s] = array_pad(explode(':', $hms), 3, 0);
                $total += ((int) $h * 3600) + ((int) $m * 60) + (int) $s;
            }
        }

        $hours = floor($total / 3600);
        $minutes = floor(($total % 3600) / 60);
        $seconds = $total % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }
}

/**
 * Convert "HH:MM:SS" into human-friendly text (e.g., "1 hr 05 min", "30 sec").
 *
 * @param  string|null  $duration
 * @return string
 */
if (! function_exists('readable_time_for_humans')) {
    function readable_time_for_humans($duration)
    {
        if (! $duration) {
            return '00:00';
        }

        [$h, $m, $s] = array_pad(explode(':', $duration), 3, 0);
        $h = (int) $h;
        $m = (int) $m;
        $s = (int) $s;

        if ($h > 0) {
            return $h.' '.get_phrase('hr').' '.str_pad((string) $m, 2, '0', STR_PAD_LEFT).' '.get_phrase('min');
        }

        if ($m > 0) {
            // original behavior: if seconds > 0, round up minute by +1
            $rounded = $s > 0 ? $m + 1 : $m;

            return $rounded.' '.get_phrase('min');
        }

        if ($s > 0) {
            return $s.' '.get_phrase('sec');
        }

        return '00:00';
    }
}

/**
 * Remove any <script>...</script> tags from a string (basic XSS guard for embedded HTML).
 *
 * @param  string  $description
 * @return string
 */
if (! function_exists('remove_js')) {
    function remove_js($input = '')
    {
        if (! is_string($input)) {
            return $input;
        }

        return preg_replace('#<script(.*?)>(.*?)</script>#is', '', $input);
    }
}

/**
 * Check if a course is present in the user's cart.
 *
 * @param  int|string  $userId
 * @param  int|string  $courseId
 * @return bool
 */
if (! function_exists('is_cart_item')) {
    function is_cart_item($userId = '', $courseId = '')
    {
        return CartItem::query()
            ->where('course_id', $courseId)
            ->where('user_id', $userId)
            ->exists();
    }
}
