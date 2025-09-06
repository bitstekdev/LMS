<?php

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogComment;
use App\Models\BlogLike;
use App\Models\Bootcamp;
use App\Models\BootcampLiveClass;
use App\Models\BootcampModule;
use App\Models\BootcampPurchase;
use App\Models\BootcampResource;
use App\Models\Category;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Currency;
use App\Models\Enrollment;
use App\Models\FrontendSetting;
use App\Models\HomePageSetting;
use App\Models\Language;
use App\Models\LanguagePhrase;
use App\Models\Lesson;
use App\Models\MediaFile;
use App\Models\PaymentHistory;
use App\Models\Payout;
use App\Models\Permission;
use App\Models\PlayerSetting;
use App\Models\Review;
use App\Models\Section;
use App\Models\Setting;
use App\Models\TeamPackageMember;
use App\Models\TeamPackagePurchase;
use App\Models\TeamTrainingPackage;
use App\Models\TutorBooking;
use App\Models\TutorReview;
use App\Models\TutorSchedule;
use App\Models\User;
use App\Models\WatchHistory;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Return an optimized/public image URL proxy.
 *
 * @param  string|null  $url
 * @param  bool  $optimized
 * @return string
 */
if (! function_exists('get_src')) {
    function get_src($url = null, bool $optimized = false)
    {
        return get_image($url, $optimized);
    }
}

/**
 * Count lessons in a course.
 *
 * @param  int|string  $courseId
 * @return int
 */
if (! function_exists('lesson_count')) {
    function lesson_count($courseId = '')
    {
        if ($courseId === '') {
            return 0;
        }

        return Lesson::where('course_id', $courseId)->count();
    }
}

/**
 * Count sections in a course.
 *
 * @param  int|string  $courseId
 * @return int
 */
if (! function_exists('section_count')) {
    function section_count($courseId = '')
    {
        if ($courseId === '') {
            return 0;
        }

        return Section::where('course_id', $courseId)->count();
    }
}

/**
 * Count published blogs for a category.
 *
 * @param  int|string  $categoryId
 * @return int
 */
if (! function_exists('count_blogs_by_category')) {
    function count_blogs_by_category($categoryId = '')
    {
        if ($categoryId === '') {
            return 0;
        }

        return Blog::where('status', 1)->where('category_id', $categoryId)->count();
    }
}

/**
 * Get blog category title.
 *
 * @param  int|string  $id
 * @return string|null
 */
if (! function_exists('get_blog_category_name')) {
    function get_blog_category_name($id = '')
    {
        if ($id === '') {
            return null;
        }

        return BlogCategory::whereKey($id)->value('title');
    }
}

/**
 * Fetch a user by id (or empty model).
 *
 * @param  int|string  $userId
 * @return \App\Models\User
 */
if (! function_exists('get_user_info')) {
    function get_user_info($userId = '')
    {
        return User::whereKey($userId)->firstOrNew();
    }
}

/**
 * Get a user's image URL by user id.
 *
 * @param  int|string  $userId
 * @return string
 */
if (! function_exists('get_image_by_id')) {
    function get_image_by_id($userId = '')
    {
        $imagePath = User::whereKey($userId)->value('photo');

        return get_image($imagePath);
    }
}

/**
 * Human "time ago" formatter from a date/time string.
 *
 * @param  string  $time_ago
 * @return string
 */
if (! function_exists('timeAgo')) {
    function timeAgo($time_ago)
    {
        $time_ago = is_numeric($time_ago) ? (int) $time_ago : strtotime($time_ago);
        $cur_time = time();
        $elapsed = $cur_time - $time_ago;

        $years = round($elapsed / 31207680);
        $months = round($elapsed / 2600640);
        $weeks = round($elapsed / 604800);
        $days = round($elapsed / 86400);
        $hours = round($elapsed / 3600);
        $mins = round($elapsed / 60);
        $secs = $elapsed;

        if ($secs <= 60) {
            return 'just now';
        }
        if ($mins <= 60) {
            return $mins == 1 ? '1 minute ago' : "$mins minutes ago";
        }
        if ($hours <= 24) {
            return $hours == 1 ? '1 hour ago' : "$hours hours ago";
        }
        if ($days <= 7) {
            return $days == 1 ? 'Yesterday' : "$days days ago";
        }
        if ($weeks <= 4.3) {
            return $weeks == 1 ? '1 week ago' : "$weeks weeks ago";
        }
        if ($months <= 12) {
            return $months == 1 ? '1 month ago' : "$months months ago";
        }

        return $years == 1 ? '1 year ago' : "$years years ago";
    }
}

/**
 * Does the user have any enrollments?
 *
 * @param  int|string  $userId
 * @return bool
 */
if (! function_exists('course_enrolled')) {
    function course_enrolled($userId = '')
    {
        if ($userId === '') {
            return false;
        }

        return Enrollment::where('user_id', $userId)->exists();
    }
}

/**
 * Count enrollments for a course.
 *
 * @param  int|string  $courseId
 * @return int
 */
if (! function_exists('course_enrollments')) {
    function course_enrollments($courseId = '')
    {
        if ($courseId === '') {
            return 0;
        }

        return Enrollment::where('course_id', $courseId)->count();
    }
}

/**
 * Get a course's instructor user (owner).
 *
 * @param  int|string  $courseId
 * @return \App\Models\User
 */
if (! function_exists('course_by_instructor')) {
    function course_by_instructor($courseId = '')
    {
        if ($courseId === '') {
            return new User;
        }
        $course = Course::whereKey($courseId)->firstOrNew();

        return User::whereKey($course->user_id)->firstOrNew();
    }
}

/**
 * Get a course instructor image URL.
 *
 * @param  int|string  $courseId
 * @return string
 */
if (! function_exists('course_instructor_image')) {
    function course_instructor_image($courseId = '')
    {
        if ($courseId === '') {
            return get_image(null);
        }
        $userId = Course::whereKey($courseId)->value('user_id');
        $userImage = User::whereKey($userId)->value('photo');

        return get_image($userImage);
    }
}

/**
 * Get a Course model (or empty).
 *
 * @param  int|string  $courseId
 * @return \App\Models\Course
 */
if (! function_exists('get_course_info')) {
    function get_course_info($courseId)
    {
        return Course::whereKey($courseId)->firstOrNew();
    }
}

/**
 * Count unread messages in a thread for logged user.
 *
 * @param  string  $threadCode
 * @return int
 */
if (! function_exists('count_unread_message_of_thread')) {
    function count_unread_message_of_thread($threadCode = '')
    {
        if ($threadCode === '') {
            return 0;
        }
        $currentUser = auth('web')->id();

        // If you have a Message model with fields: message_thread_code, sender, read_status
        return \App\Models\Message::where('message_thread_code', $threadCode)
            ->where('sender', '!=', $currentUser)
            ->where('read_status', '0')
            ->count();
    }
}

/**
 * Get an enrollment row for a user and course (or empty).
 *
 * @param  int|string  $courseId
 * @param  int|string  $userId
 * @return \App\Models\Enrollment
 */
if (! function_exists('get_enroll_info')) {
    function get_enroll_info($courseId = '', $userId = '')
    {
        if ($courseId === '' || $userId === '') {
            return new Enrollment;
        }

        return Enrollment::where('course_id', $courseId)->where('user_id', $userId)->firstOrNew();
    }
}

/**
 * Count all enrollments.
 *
 * @return int
 */
if (! function_exists('total_enrolled')) {
    function total_enrolled()
    {
        return Enrollment::count();
    }
}

/**
 * Count enrollments for a course.
 *
 * @param  int|string  $courseId
 * @return int
 */
if (! function_exists('total_enroll')) {
    function total_enroll($courseId = '')
    {
        if ($courseId === '') {
            return 0;
        }

        return Enrollment::where('course_id', $courseId)->count();
    }
}

/**
 * Check if user is a course instructor (owner or in instructors relation).
 *
 * @param  int|string  $courseId
 * @param  int|string  $userId
 * @return bool
 */
if (! function_exists('is_course_instructor')) {
    function is_course_instructor($courseId = '', $userId = '')
    {
        $userId = $userId ?: auth('web')->id();
        if (! $userId || $courseId === '') {
            return false;
        }

        $course = Course::whereKey($courseId)->first();
        if (! $course) {
            return false;
        }

        // owner or in instructors (if you have pivot)
        if ((int) $course->user_id === (int) $userId) {
            return true;
        }

        // If you maintain instructors list as JSON IDs, adjust accordingly:
        if ($course->instructor_ids) {
            $ids = json_decode($course->instructor_ids, true) ?: [];

            return in_array((string) $userId, array_map('strval', $ids), true);
        }

        // Or if relation exists:
        // return $course->instructors()->whereKey($userId)->exists();

        return false;
    }
}

/**
 * Get homepage settings by key.
 *
 * @param  string  $key
 * @param  bool|mixed  $returnType  true=array, "object"=object, false=raw string
 * @return mixed
 */
if (! function_exists('get_homepage_settings')) {
    function get_homepage_settings($key = '', $returnType = false)
    {
        $q = HomePageSetting::where('key', $key);

        if (! $q->exists()) {
            return false;
        }

        $val = $q->value('value');
        if ($returnType === true) {
            return json_decode($val, true);
        }
        if ($returnType === 'object') {
            return json_decode($val);
        }

        return $val;
    }
}

/**
 * Count distinct students enrolled in any course by instructor.
 *
 * @param  int|string  $userId
 * @return string
 */
if (! function_exists('count_student_by_instructor')) {
    function count_student_by_instructor($userId = '')
    {
        if ($userId === '') {
            return '0 '.get_phrase('Student');
        }

        $courseIds = Course::where('user_id', $userId)->pluck('id');
        $total = Enrollment::whereIn('course_id', $courseIds)->distinct('user_id')->count('user_id');

        return $total > 1 ? "{$total} ".get_phrase('Students') : "{$total} ".get_phrase('Student');
    }
}

/**
 * Count active courses by instructor.
 *
 * @param  int|string  $userId
 * @return string
 */
if (! function_exists('count_course_by_instructor')) {
    function count_course_by_instructor($userId = '')
    {
        if ($userId === '') {
            return '0 '.get_phrase('Course');
        }
        $count = Course::where('status', 'active')->where('user_id', $userId)->count();

        return $count > 1 ? "{$count} ".get_phrase('Courses') : "{$count} ".get_phrase('Course');
    }
}

/**
 * Course progress for the logged user in a course (percentage 0..100).
 *
 * @param  int|string  $courseId
 * @return string number_format(2)
 */
if (! function_exists('progress_bar')) {
    function progress_bar($courseId = '')
    {
        if ($courseId === '') {
            return '0.00';
        }

        $history = WatchHistory::where('course_id', $courseId)
            ->where('student_id', auth('web')->id())
            ->first();

        $totalLessons = lesson_count($courseId);
        $progress = 0;

        if ($history && $history->completed_lesson) {
            $completedIds = json_decode($history->completed_lesson, true) ?: [];
            $incompleteCount = Lesson::where('course_id', $courseId)->whereNotIn('id', $completedIds)->count();
            $countComplete = $totalLessons - $incompleteCount;

            // Keep completed list consistent if lessons were deleted
            if (count($completedIds) !== $countComplete) {
                check_lesson_was_deleted($completedIds, $history->id);
            }

            $progress = $totalLessons ? (count($completedIds) * 100) / $totalLessons : 0;
        }

        return $progress <= 100 ? number_format($progress, 2) : '100.00';
    }
}

/**
 * Purge non-existing lesson ids from a WatchHistory row.
 *
 * @param  array  $completedIds
 * @param  int|string  $historyId
 * @return void
 */
if (! function_exists('check_lesson_was_deleted')) {
    function check_lesson_was_deleted(array $completedIds = [], $historyId = '')
    {
        $updated = [];
        foreach ($completedIds as $id) {
            if (Lesson::whereKey($id)->exists()) {
                $updated[] = $id;
            }
        }
        WatchHistory::whereKey($historyId)->update(['completed_lesson' => json_encode($updated)]);
    }
}

/**
 * Does the user own at least one course? (creator)
 *
 * @param  int|string  $userId
 * @return bool
 */
if (! function_exists('course_creator')) {
    function course_creator($userId = '')
    {
        if ($userId === '') {
            return false;
        }

        return Course::where('user_id', $userId)->exists();
    }
}

/**
 * Get the course creator user by course id.
 *
 * @param  int|string  $courseId
 * @return \App\Models\User|null
 */
if (! function_exists('get_course_creator_id')) {
    function get_course_creator_id($courseId = '')
    {
        if ($courseId === '') {
            return null;
        }
        $userId = Course::whereKey($courseId)->value('user_id');

        return $userId ? User::whereKey($userId)->firstOrNew() : null;
    }
}

/**
 * Count users by role.
 *
 * @param  string  $role
 * @return int
 */
if (! function_exists('user_count')) {
    function user_count($role = '')
    {
        if ($role === '') {
            return 0;
        }

        return User::where('role', $role)->count();
    }
}

/**
 * Get a blog user by id.
 *
 * @param  int|string  $userId
 * @return \App\Models\User
 */
if (! function_exists('blog_user')) {
    function blog_user($userId = '')
    {
        if ($userId === '') {
            return new User;
        }

        return User::whereKey($userId)->firstOrNew();
    }
}

/**
 * Count courses under a category slug (including subcategories).
 *
 * @param  string  $slug
 * @return int
 */
if (! function_exists('category_course_count')) {
    function category_course_count($slug = '')
    {
        if ($slug === '') {
            return 0;
        }

        $category = Category::where('slug', $slug)->first();
        if (! $category) {
            return 0;
        }

        $ids = Category::where('parent_id', $category->id)->pluck('id')->toArray();
        $ids[] = $category->id;

        return Course::whereIn('category_id', $ids)->count();
    }
}

/**
 * Get a category (firstOrNew) by id.
 *
 * @param  int|string  $categoryId
 * @return \App\Models\Category
 */
if (! function_exists('category_by_course')) {
    function category_by_course($categoryId = '')
    {
        return Category::whereKey($categoryId)->firstOrNew();
    }
}

/**
 * Return a user's role (or null).
 *
 * @param  int|string  $userId
 * @return string|null
 */
if (! function_exists('check_course_admin')) {
    function check_course_admin($userId = '')
    {
        if ($userId === '') {
            return null;
        }

        return User::whereKey($userId)->value('role');
    }
}

/**
 * Convert "HH:MM:SS" to seconds.
 *
 * @param  string  $duration
 * @return int
 */
if (! function_exists('duration_to_seconds')) {
    function duration_to_seconds($duration = '00:00:00')
    {
        if ($duration === '') {
            $duration = '00:00:00';
        }
        [$h, $m, $s] = array_pad(explode(':', $duration), 3, 0);

        return ((int) $h * 3600) + ((int) $m * 60) + (int) $s;
    }
}

/**
 * Total duration for a course as "HHh MMm".
 *
 * @param  int|string  $courseId
 * @return string
 */
if (! function_exists('total_durations')) {
    function total_durations($courseId = '')
    {
        $seconds = Lesson::where('course_id', $courseId)
            ->pluck('duration')
            ->filter()
            ->sum(fn ($d) => duration_to_seconds($d));

        $h = floor($seconds / 3600);
        $m = floor(($seconds % 3600) / 60);

        return sprintf('%02dh %02dm', $h, $m);
    }
}

/**
 * Total duration for a course as "HH:MM:SS".
 *
 * @param  int|string  $courseId
 * @return string
 */
if (! function_exists('total_durations_by')) {
    function total_durations_by($courseId = '')
    {
        $seconds = Lesson::where('course_id', $courseId)
            ->pluck('duration')
            ->filter()
            ->sum(fn ($d) => duration_to_seconds($d));

        $h = floor($seconds / 3600);
        $m = floor(($seconds % 3600) / 60);
        $s = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $h, $m, $s);
    }
}

/**
 * Duration for a single lesson as "HH:MM:SS".
 *
 * @param  int|string  $lessonId
 * @return string
 */
if (! function_exists('lesson_durations')) {
    function lesson_durations($lessonId = '')
    {
        $lesson = Lesson::whereKey($lessonId)->firstOrNew();
        $seconds = $lesson->duration ? duration_to_seconds($lesson->duration) : 0;

        $h = floor($seconds / 3600);
        $m = floor(($seconds % 3600) / 60);
        $s = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $h, $m, $s);
    }
}

/**
 * Is the given admin id the very first (root) admin?
 *
 * @param  int|string|null  $adminId
 * @return bool
 */
if (! function_exists('is_root_admin')) {
    function is_root_admin($adminId = null)
    {
        $adminId = $adminId ?: auth('web')->id();
        $root = User::orderBy('id', 'asc')->first();

        return $root && (int) $root->id === (int) $adminId;
    }
}

/**
 * Strip script tags/handlers/JS URIs from content.
 *
 * @param  string|null  $text
 * @return string|null
 */
if (! function_exists('removeScripts')) {
    function removeScripts($text)
    {
        if (! $text) {
            return $text;
        }

        $clean = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/is', '', $text);
        $clean = preg_replace('/\s*on\w+="[^"]*"/i', '', $clean);
        $clean = preg_replace('/\s*href="javascript:[^"]*"/i', '', $clean);
        $clean = preg_replace('/<(object|applet|meta|link|style|base|form)\b[^<]*(?:(?!<\/\1>)<[^<]*)*<\/\1>/is', '', $clean);

        return $clean;
    }
}

/**
 * Check route permission for an admin.
 *
 * @param  string  $route
 * @param  int|string|null  $userId
 * @return bool
 */
if (! function_exists('has_permission')) {
    function has_permission($route = '', $userId = null)
    {
        $userId = $userId ?: auth('web')->id();

        if (is_root_admin($userId)) {
            return true;
        }

        $permRow = Permission::where('admin_id', $userId)->first();
        if (! $permRow) {
            return false;
        }

        $perms = json_decode($permRow->permissions, true);

        return is_array($perms) ? in_array($route, $perms, true) : false;
    }
}

/**
 * Resolve an image asset URL with optional "optimized" fallback.
 *
 * @param  string|null  $url
 * @param  bool  $optimized
 * @return string
 */
if (! function_exists('get_image')) {
    function get_image($url = null, bool $optimized = false)
    {
        if ($url === null || $url === '') {
            return asset('uploads/system/placeholder.png');
        }

        // Remote URL?
        if (Str::startsWith($url, ['http://', 'https://'])) {
            return $url;
        }

        $url = ltrim($url, '/');
        $parts = explode('/', $url);
        $fileName = end($parts);
        $path = Str::replaceLast($fileName, '', $url);
        $optimizedPath = $path.'optimized/'.$fileName;

        if (! $optimized) {
            return File::isFile(public_path($url)) ? asset($url) : asset($path.'placeholder/placeholder.png');
        }

        return File::isFile(public_path($optimizedPath))
            ? asset($optimizedPath)
            : asset($path.'placeholder/placeholder.png');
    }
}

/**
 * Build a nice file name from a title and extension.
 *
 * @param  string  $fileTitle
 * @param  string  $extension
 * @return string
 */
if (! function_exists('nice_file_name')) {
    function nice_file_name($fileTitle = '', $extension = '')
    {
        return slugify($fileTitle).'-'.time().'.'.ltrim($extension, '.');
    }
}

/**
 * Count reviews of a course.
 *
 * @param  int|string  $courseId
 * @return int
 */
if (! function_exists('total_review')) {
    function total_review($courseId = '')
    {
        return Review::where('course_id', $courseId)->count();
    }
}

/**
 * Physically remove a file (and its optimized version) under public/.
 *
 * @param  string|null  $url
 * @return void
 */
if (! function_exists('remove_file')) {
    function remove_file($url = null)
    {
        if (! $url) {
            return;
        }

        $url = str_replace('public/', '', $url);
        $url = str_replace('optimized/', '', $url);

        $full = public_path($url);
        $name = basename($full);

        if ($name && File::isFile($full)) {
            @unlink($full);

            $opt = Str::replaceLast($name, 'optimized/'.$name, $full);
            if (File::isFile($opt)) {
                @unlink($opt);
            }
        }
    }
}

/**
 * Get all language names.
 *
 * @return \Illuminate\Support\Collection
 */
if (! function_exists('get_all_language')) {
    function get_all_language()
    {
        return Language::select('name')->distinct()->get();
    }
}

/**
 * Translate a phrase using active language with fallback + placeholder replacement.
 *
 * @param  string  $phrase
 * @param  array  $replacements
 * @return string
 */
if (! function_exists('get_phrase')) {
    function get_phrase($phrase = '', $replacements = [])
    {
        $active = session('language') ?? get_settings('language');
        $langId = Language::where('name', 'like', $active)->value('id');

        $row = LanguagePhrase::where('language_id', $langId)->where('phrase', $phrase)->first();

        if ($row) {
            $translated = $row->translated;
        } else {
            $translated = $phrase;

            // ensure exists in English phrases
            $en = Language::where('name', 'like', 'english')->first();
            if ($en && ! LanguagePhrase::where('language_id', $en->id)->where('phrase', $phrase)->exists()) {
                LanguagePhrase::create([
                    'language_id' => $en->id,
                    'phrase' => $phrase,
                    'translated' => $translated,
                ]);
            }
        }

        if (! is_array($replacements)) {
            $replacements = [$replacements];
        }
        foreach ($replacements as $replace) {
            $translated = preg_replace('/____/', (string) $replace, $translated, 1);
        }

        return $translated;
    }
}

/**
 * Basic XSS guard for display (optional conversion).
 *
 * @param  string  $string
 * @param  bool  $convert
 * @return string
 */
if (! function_exists('script_checker')) {
    function script_checker($string = '', $convert = true)
    {
        return $convert ? nl2br(htmlspecialchars(strip_tags($string))) : $string;
    }
}

/**
 * Get user by blog comment author id.
 *
 * @param  int|string  $userId
 * @return \App\Models\User
 */
if (! function_exists('get_user_by_blogcomment')) {
    function get_user_by_blogcomment($userId = '')
    {
        return User::whereKey($userId)->firstOrNew();
    }
}

/**
 * Date formatter with presets.
 *
 * @param  int|string  $strtotime
 * @param  int|string  $format
 * @return string
 */
if (! function_exists('date_formatter')) {
    function date_formatter($strtotime = '', $format = '')
    {
        if ($strtotime && ! is_numeric($strtotime)) {
            $strtotime = strtotime($strtotime);
        } elseif (! $strtotime) {
            $strtotime = time();
        }

        if ($format === '') {
            return date('d M Y', $strtotime);
        }

        if ((int) $format === 1) {
            return date('D, d M Y', $strtotime);
        }

        if ((int) $format === 2) {
            $diff = time() - $strtotime;
            if ($diff <= 10) {
                return get_phrase('Just now');
            }
            if ($diff > 864000) {
                return date_formatter($strtotime, 3);
            }

            $units = [
                12 * 30 * 24 * 60 * 60 => get_phrase('year'),
                30 * 24 * 60 * 60 => get_phrase('month'),
                24 * 60 * 60 => get_phrase('day'),
                60 * 60 => 'hour',
                60 => 'minute',
                1 => 'second',
            ];
            foreach ($units as $secs => $str) {
                $d = $diff / $secs;
                if ($d >= 1) {
                    $t = round($d);

                    return $t.' '.$str.($t > 1 ? 's' : '').' '.get_phrase('ago');
                }
            }
        }

        if ((int) $format === 3) {
            $date = date('d M', $strtotime);
            if (date('Y', $strtotime) != date('Y')) {
                $date .= date(' Y', $strtotime);
            }

            return $date.' '.get_phrase('at').' '.date('h:i a', $strtotime);
        }

        if ((int) $format === 4) {
            return date('d M Y, h:i:s A', $strtotime);
        }

        return date('d M Y', $strtotime);
    }
}

/**
 * Currency formatter using settings (symbol + position).
 *
 * @param  float|int|string  $price
 * @return string
 */
if (! function_exists('currency')) {
    function currency($price = 0)
    {
        $pattern = get_settings('currency_position');
        $symbol = Currency::where('code', get_settings('system_currency'))->value('symbol');

        $price = (float) $price;
        $formatted = number_format($price, 2, '.', '');

        return match ($pattern) {
            'right' => $formatted.$symbol,
            'left' => $symbol.$formatted,
            'right-space' => $formatted.' '.$symbol,
            'left-space' => $symbol.' '.$formatted,
            default => $symbol.$formatted,
        };
    }
}

/**
 * Slugify with Unicode support (using intl Normalizer).
 *
 * @param  string  $string
 * @return string
 */
if (! function_exists('slugify')) {
    function slugify($string)
    {
        $string = \Normalizer::normalize(trim((string) $string), \Normalizer::FORM_C);
        $slug = preg_replace('/[\s-]+/u', '-', $string);
        $slug = preg_replace('/[^\p{L}\p{M}\p{N}-]/u', '', $slug);

        return mb_strtolower($slug, 'UTF-8');
    }
}

/**
 * Truncate a string with ellipsis.
 *
 * @param  string  $long
 * @param  int  $max
 * @return string
 */
if (! function_exists('ellipsis')) {
    function ellipsis($long, $max = 30)
    {
        $long = strip_tags((string) $long);

        return mb_strlen($long) > $max ? mb_substr($long, 0, $max).'...' : $long;
    }
}

/**
 * Simple htmlspecialchar decode wrapper.
 *
 * @param  string|null  $description
 * @return string
 */
if (! function_exists('htmlspecialchars_decode_')) {
    function htmlspecialchars_decode_($description = '')
    {
        return htmlspecialchars_decode($description ?? '');
    }
}

/**
 * Random alphanumeric string (optionally lowercased).
 *
 * @param  int  $length
 * @param  bool  $lowercase
 * @return string
 */
if (! function_exists('random')) {
    function random($length, $lowercase = false)
    {
        $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $val = substr(str_shuffle($chars), 0, max(1, (int) $length));

        return $lowercase ? strtolower($val) : $val;
    }
}

/**
 * System settings getter.
 *
 * @param  string  $type
 * @param  mixed  $returnType  true=array, "object"=object, false=raw
 * @return mixed
 */
if (! function_exists('get_settings')) {
    function get_settings($type = '', $returnType = false)
    {
        $q = Setting::where('type', $type);

        if (! $q->exists()) {
            return false;
        }

        $val = $q->value('description');
        if ($returnType === true) {
            return json_decode($val, true);
        }
        if ($returnType === 'object') {
            return json_decode($val);
        }

        return $val;
    }
}

/**
 * Lesson completion for a user (1/0). Infers course when omitted.
 *
 * @param  int|string  $lessonId
 * @param  int|string|null  $userId
 * @param  int|string|null  $courseId
 * @return int
 */
if (! function_exists('lesson_progress')) {
    function lesson_progress($lessonId = '', $userId = null, $courseId = null)
    {
        $userId = $userId ?: auth('web')->id();
        $courseId = $courseId ?: Lesson::whereKey($lessonId)->value('course_id');

        $watch = WatchHistory::where('student_id', $userId)
            ->where('course_id', $courseId)
            ->first();

        if ($watch && $watch->completed_lesson) {
            $ids = json_decode($watch->completed_lesson, true) ?: [];

            return (is_array($ids) && in_array($lessonId, $ids, true)) ? 1 : 0;
        }

        return 0;
    }
}

/**
 * Frontend settings getter.
 *
 * @param  string  $key
 * @param  mixed  $returnType
 * @return mixed
 */
if (! function_exists('get_frontend_settings')) {
    function get_frontend_settings($key = '', $returnType = false)
    {
        $q = FrontendSetting::where('key', $key);
        if (! $q->exists()) {
            return false;
        }

        $val = $q->value('value');
        if ($returnType === true) {
            return json_decode($val, true);
        }
        if ($returnType === 'object') {
            return json_decode($val);
        }

        return $val;
    }
}

/**
 * Is email already registered?
 *
 * @param  string  $email
 * @return bool
 */
if (! function_exists('check_registered')) {
    function check_registered($email = '')
    {
        if ($email === '') {
            return false;
        }

        return User::where('email', $email)->exists();
    }
}

/**
 * Basic permission check for admin by permission name.
 *
 * @param  string  $permissionFor
 * @param  int|string|null  $adminId
 * @return bool
 */
if (! function_exists('is_permission')) {
    function is_permission($permissionFor = '', $adminId = null)
    {
        $adminId = $adminId ?: auth('web')->id();

        if (is_root_admin($adminId)) {
            return true;
        }

        $perm = Permission::where('admin_id', $adminId)->first();
        if (! $perm) {
            return false;
        }

        $arr = json_decode($perm->permissions, true);

        return is_array($arr) && in_array($permissionFor, $arr, true);
    }
}

/**
 * Count comments for blog id as readable text (e.g., "3 comments").
 *
 * @param  int|string  $blogId
 * @return string
 */
if (! function_exists('count_comments_by_blog_id')) {
    function count_comments_by_blog_id($blogId)
    {
        $count = BlogComment::where('blog_id', $blogId)->count();
        $text = $count > 1 ? get_phrase('comments') : get_phrase('comment');

        return format_count($count).' '.$text;
    }
}

/**
 * Count likes for blog id as readable text (e.g., "5 likes").
 *
 * @param  int|string  $blogId
 * @return string
 */
if (! function_exists('count_likes_by_blog_id')) {
    function count_likes_by_blog_id($blogId)
    {
        $count = BlogLike::where('blog_id', $blogId)->count();
        $text = $count > 1 ? get_phrase('likes') : get_phrase('like');

        return format_count($count).' '.$text;
    }
}

/**
 * Format a number with K/M/B suffixes.
 *
 * @param  int|float  $num
 * @return string|int|float
 */
if (! function_exists('format_count')) {
    function format_count($num)
    {
        $num = (int) $num;
        if ($num >= 1000000000) {
            return round($num / 1000000000, 1).get_phrase('B');
        }
        if ($num >= 1000000) {
            return round($num / 1000000, 1).get_phrase('M');
        }
        if ($num >= 1000) {
            return round($num / 1000, 1).get_phrase('K');
        }

        return $num;
    }
}

/**
 * Get chat media files by conversation id.
 *
 * @param  int|string  $conversationId
 * @return \Illuminate\Support\Collection
 */
if (! function_exists('get_files')) {
    function get_files($conversationId)
    {
        return MediaFile::where('chat_id', $conversationId)->get();
    }
}

/**
 * Instructor experience based on first course creation date.
 *
 * @param  int|string  $userId
 * @return string
 */
if (! function_exists('instructor_experience')) {
    function instructor_experience($userId)
    {
        $first = Course::where('user_id', $userId)->oldest('id')->first();
        if (! $first) {
            return get_phrase('Recently appointed');
        }

        $diff = time() - strtotime($first->created_at);
        $years = floor($diff / 31536000);
        if ($years >= 1) {
            return $years === 1 ? '1 year' : $years.' years';
        }

        $months = floor($diff / 2592000);

        return $months <= 1 ? '1 month' : $months.' months';
    }
}

/**
 * Count reviews received by instructor across their courses.
 *
 * @param  int|string  $userId
 * @return string
 */
if (! function_exists('instructor_reviews')) {
    function instructor_reviews($userId)
    {
        $count = Review::join('courses', 'reviews.course_id', '=', 'courses.id')
            ->where('courses.user_id', $userId)
            ->count();

        return $count > 1 ? "{$count} ".get_phrase('reviews') : "{$count} ".get_phrase('review');
    }
}

/**
 * Average instructor rating across their courses (simple sum/5 formatted).
 *
 * @param  int|string  $userId
 * @return string
 */
if (! function_exists('instructor_rating')) {
    function instructor_rating($userId)
    {
        $sum = Review::join('courses', 'reviews.course_id', '=', 'courses.id')
            ->where('courses.user_id', $userId)
            ->sum('reviews.rating');

        return number_format(($sum / 5), 1);
    }
}

/**
 * Count instructor lessons across all their courses.
 *
 * @param  int|string  $userId
 * @return string
 */
if (! function_exists('count_instructor_lesson')) {
    function count_instructor_lesson($userId)
    {
        $courseIds = Course::where('user_id', $userId)->pluck('id');
        $count = Lesson::whereIn('course_id', $courseIds)->count();

        return $count > 1 ? "{$count} ".get_phrase('lessons') : "{$count} ".get_phrase('lesson');
    }
}

/**
 * Count active courses within a category (parent includes children).
 *
 * @param  int|string  $categoryId
 * @return int|string
 */
if (! function_exists('count_category_courses')) {
    function count_category_courses($categoryId)
    {
        $cat = Category::whereKey($categoryId)->first();
        if (! $cat) {
            return '0';
        }

        if ($cat->parent_id > 0) {
            return Course::where('status', 'active')->where('category_id', $categoryId)->count();
        }

        $ids = Category::where('parent_id', $categoryId)->pluck('id')->toArray();
        $ids[] = (int) $categoryId;

        return Course::where('status', 'active')->whereIn('category_id', $ids)->count();
    }
}

/**
 * Count certificates for a user (pluralized text).
 *
 * @param  int|string  $userId
 * @return string
 */
if (! function_exists('count_user_certificate')) {
    function count_user_certificate($userId)
    {
        $count = Certificate::where('user_id', $userId)->count();

        return $count > 1 ? "{$count} ".get_phrase('certificates') : "{$count} ".get_phrase('certificate');
    }
}

/**
 * Top categories from recent payments (grouped & sorted).
 *
 * @return \Illuminate\Support\Collection|Category[]
 */
if (! function_exists('top_categories')) {
    function top_categories()
    {
        $data = PaymentHistory::join('courses', 'payment_histories.course_id', '=', 'courses.id')
            ->join('categories', 'courses.category_id', '=', 'categories.id')
            ->select('courses.category_id')
            ->take(200) // a sane cap
            ->get();

        $sorted = $data->groupBy('category_id')->map->count()->sortDesc()->keys();

        return $sorted->isNotEmpty()
            ? Category::whereIn('id', $sorted)->get()
            : collect();
    }
}

/**
 * Collect up to 15 unique blog tags from JSON "keywords".
 *
 * @return \Illuminate\Support\Collection
 */
if (! function_exists('get_blog_tags')) {
    function get_blog_tags()
    {
        return Blog::whereNotNull('keywords')
            ->get(['keywords'])
            ->flatMap(fn ($b) => collect(json_decode($b->keywords, true) ?: []))
            ->pluck('value')
            ->unique()
            ->take(15)
            ->values();
    }
}

/**
 * Replace reserved URL symbols with hyphens.
 *
 * @param  string  $str
 * @return string
 */
if (! function_exists('replace_url_symbol')) {
    function replace_url_symbol($str)
    {
        return preg_replace('/[\?#&\/:@=%]/', '-', (string) $str);
    }
}

/**
 * Count bootcamps by category.
 *
 * @param  int|string  $categoryId
 * @return int
 */
if (! function_exists('count_bootcamps_by_category')) {
    function count_bootcamps_by_category($categoryId = '')
    {
        if ($categoryId === '') {
            return 0;
        }

        return Bootcamp::where('status', 1)->where('category_id', $categoryId)->count();
    }
}

/**
 * Count bootcamp modules (optionally filtered by bootcamp).
 *
 * @param  int|string|null  $bootcampId
 * @return int
 */
if (! function_exists('count_bootcamp_modules')) {
    function count_bootcamp_modules($bootcampId = '')
    {
        $q = BootcampModule::query();
        if ($bootcampId) {
            $q->where('bootcamp_id', $bootcampId);
        }

        return $q->count();
    }
}

/**
 * Count bootcamp live classes by bootcamp or module.
 *
 * @param  int|string|null  $id
 * @param  'bootcamp'|'module'  $type
 * @return int
 */
if (! function_exists('count_bootcamp_classes')) {
    function count_bootcamp_classes($id = '', $type = 'bootcamp')
    {
        $q = BootcampLiveClass::join('bootcamp_modules', 'bootcamp_live_classes.module_id', '=', 'bootcamp_modules.id')
            ->join('bootcamps', 'bootcamp_modules.bootcamp_id', '=', 'bootcamps.id');

        if ($id && $type === 'bootcamp') {
            $q->where('bootcamp_modules.bootcamp_id', $id);
        }
        if ($id && $type === 'module') {
            $q->where('bootcamp_live_classes.module_id', $id);
        }

        return $q->count();
    }
}

/**
 * Current theme view path prefix.
 *
 * @return string
 */
if (! function_exists('theme_path')) {
    function theme_path()
    {
        return 'frontend.'.get_frontend_settings('theme').'.';
    }
}

/**
 * Has user purchased a bootcamp?
 *
 * @param  int|string  $bootcampId
 * @param  int|string|null  $userId
 * @return int number of purchases
 */
if (! function_exists('is_purchased_bootcamp')) {
    function is_purchased_bootcamp($bootcampId, $userId = null)
    {
        $userId = $userId ?? auth('web')->id();

        return BootcampPurchase::where('user_id', $userId)->where('bootcamp_id', $bootcampId)->count();
    }
}

/**
 * Count bootcamp enrolls (purchases scoped to bootcamp owner).
 *
 * @param  int|string  $bootcampId
 * @param  int|string|null  $userId
 * @return int
 */
if (! function_exists('bootcamp_enrolls')) {
    function bootcamp_enrolls($bootcampId, $userId = null)
    {
        $bootcamp = Bootcamp::whereKey($bootcampId)->firstOrNew();
        $userId = $userId ?? $bootcamp->user_id;

        return BootcampPurchase::join('bootcamps', 'bootcamp_purchases.bootcamp_id', '=', 'bootcamps.id')
            ->where('bootcamp_purchases.bootcamp_id', $bootcampId)
            ->where('bootcamps.user_id', $userId)
            ->count();
    }
}

/**
 * Check if an online bootcamp class is currently running.
 *
 * @param  int|string  $classId
 * @return bool|null
 */
if (! function_exists('class_started')) {
    function class_started($classId)
    {
        $now = time();
        $window = $now + (60 * 15);

        $row = BootcampLiveClass::whereKey($classId)
            ->where('force_stop', 0)
            ->whereNotNull('joining_data')
            ->where('start_time', '<', $window)
            ->where('end_time', '>', $now)
            ->first();

        return $row ? true : null;
    }
}

/**
 * Count active bootcamps created by instructor (pluralized text).
 *
 * @param  int|string  $userId
 * @return string
 */
if (! function_exists('count_instructor_bootcamps')) {
    function count_instructor_bootcamps($userId)
    {
        if ($userId === '') {
            return '0 '.get_phrase('Bootcamp');
        }
        $count = Bootcamp::where('status', 1)->where('user_id', $userId)->count();

        return $count > 1 ? "{$count} ".get_phrase('Bootcamps') : "{$count} ".get_phrase('Bootcamp');
    }
}

/**
 * Delete a bootcamp and its dependent modules/resources/live classes.
 *
 * @param  int|string  $id
 * @return void
 */
if (! function_exists('remove_bootcamp_data')) {
    function remove_bootcamp_data($id)
    {
        remove_module_data($id);
        Bootcamp::whereKey($id)->delete();
    }
}

/**
 * Delete all modules/resources/live classes for a bootcamp.
 *
 * @param  int|string  $bootcampId
 * @return void
 */
if (! function_exists('remove_module_data')) {
    function remove_module_data($bootcampId)
    {
        $modules = BootcampModule::where('bootcamp_id', $bootcampId)->get();
        foreach ($modules as $m) {
            remove_live_class_data($m->id);
            remove_resource_data($m->id);
        }
        BootcampModule::where('bootcamp_id', $bootcampId)->delete();
    }
}

/**
 * Delete all live classes for a module.
 *
 * @param  int|string  $moduleId
 * @return void
 */
if (! function_exists('remove_live_class_data')) {
    function remove_live_class_data($moduleId)
    {
        BootcampLiveClass::where('module_id', $moduleId)->delete();
    }
}

/**
 * Delete all resources for a module.
 *
 * @param  int|string  $moduleId
 * @return void
 */
if (! function_exists('remove_resource_data')) {
    function remove_resource_data($moduleId)
    {
        BootcampResource::where('module_id', $moduleId)->delete();
    }
}

/**
 * Player settings getter.
 *
 * @param  string  $title
 * @param  mixed  $returnType
 * @return mixed
 */
if (! function_exists('get_player_settings')) {
    function get_player_settings($title = '', $returnType = false)
    {
        $q = PlayerSetting::where('title', $title);
        if (! $q->exists()) {
            return false;
        }

        $val = $q->value('description');
        if ($returnType === true) {
            return json_decode($val, true);
        }
        if ($returnType === 'object') {
            return json_decode($val);
        }

        return $val;
    }
}

/**
 * Count reserved team members for a package.
 *
 * @param  int|string  $packageId
 * @return int
 */
if (! function_exists('reserved_team_members')) {
    function reserved_team_members($packageId)
    {
        return TeamPackageMember::where('team_package_id', $packageId)->count();
    }
}

/**
 * Count purchases for a team package.
 *
 * @param  int|string  $packageId
 * @return int
 */
if (! function_exists('team_package_purchases')) {
    function team_package_purchases($packageId)
    {
        return TeamPackagePurchase::where('package_id', $packageId)->count();
    }
}

/**
 * Count team training packages under a course category.
 *
 * @param  int|string  $categoryId
 * @return int
 */
if (! function_exists('team_packages_by_course_category')) {
    function team_packages_by_course_category($categoryId)
    {
        $courseIds = Course::where('category_id', $categoryId)->pluck('id');

        return TeamTrainingPackage::whereIn('course_id', $courseIds)->count();
    }
}

/**
 * Get a user's purchase record for a team package (or empty).
 *
 * @param  int|string  $packageId
 * @param  int|string|null  $userId
 * @return \App\Models\TeamPackagePurchase
 */
if (! function_exists('is_purchased_package')) {
    function is_purchased_package($packageId, $userId = null)
    {
        $userId = $userId ?? auth('web')->id();

        return TeamPackagePurchase::where('user_id', $userId)->where('package_id', $packageId)->firstOrNew();
    }
}

/**
 * Total instructor revenue from courses.
 *
 * @param  int|string|null  $userId
 * @return float|int
 */
if (! function_exists('instructor_course_revenue')) {
    function instructor_course_revenue($userId = null)
    {
        $id = $userId ?? auth('web')->id();

        return (float) Course::join('payment_histories', 'courses.id', '=', 'payment_histories.course_id')
            ->where('courses.user_id', $id)
            ->sum('payment_histories.instructor_revenue');
    }
}

/**
 * Total instructor revenue from bootcamps.
 *
 * @param  int|string|null  $userId
 * @return float|int
 */
if (! function_exists('instructor_bootcamp_revenue')) {
    function instructor_bootcamp_revenue($userId = null)
    {
        $id = $userId ?? auth('web')->id();

        return (float) BootcampPurchase::join('bootcamps', 'bootcamp_purchases.bootcamp_id', '=', 'bootcamps.id')
            ->where('bootcamps.user_id', $id)
            ->sum('bootcamp_purchases.instructor_revenue');
    }
}

/**
 * Total instructor revenue from team training packages.
 *
 * @param  int|string|null  $userId
 * @return float|int
 */
if (! function_exists('instructor_team_training_revenue')) {
    function instructor_team_training_revenue($userId = null)
    {
        $id = $userId ?? auth('web')->id();

        return (float) TeamPackagePurchase::join('team_training_packages', 'team_package_purchases.package_id', '=', 'team_training_packages.id')
            ->where('team_training_packages.user_id', $id)
            ->sum('team_package_purchases.instructor_revenue');
    }
}

/**
 * Total tutor revenue from tuition bookings.
 *
 * @param  int|string|null  $userId
 * @return float|int
 */
if (! function_exists('instructor_tution_revenue')) {
    function instructor_tution_revenue($userId = null)
    {
        $id = $userId ?? auth('web')->id();

        return (float) TutorBooking::where('tutor_id', $id)->sum('instructor_revenue');
    }
}

/**
 * Sum of all instructor revenue sources.
 *
 * @param  int|string|null  $userId
 * @return float|int
 */
if (! function_exists('instructor_total_revenue')) {
    function instructor_total_revenue($userId = null)
    {
        $id = $userId ?? auth('web')->id();

        return instructor_course_revenue($id)
             + instructor_bootcamp_revenue($id)
             + instructor_team_training_revenue($id)
             + instructor_tution_revenue($id);
    }
}

/**
 * Total paid-out amount to instructor.
 *
 * @param  int|string|null  $userId
 * @return float|int
 */
if (! function_exists('instructor_total_payout')) {
    function instructor_total_payout($userId = null)
    {
        $id = $userId ?? auth('web')->id();

        return (float) Payout::where(['user_id' => $id, 'status' => 1])->sum('amount');
    }
}

/**
 * Instructor available balance (revenue - payout).
 *
 * @param  int|string|null  $userId
 * @return float|int
 */
if (! function_exists('instructor_available_balance')) {
    function instructor_available_balance($userId = null)
    {
        $id = $userId ?? auth('web')->id();

        return instructor_total_revenue($id) - instructor_total_payout($id);
    }
}

/**
 * Count upcoming schedules for tutor from today.
 *
 * @param  int|string|null  $tutorId
 * @return int
 */
if (! function_exists('total_schedule_by_tutor_id')) {
    function total_schedule_by_tutor_id($tutorId = null)
    {
        $today = strtotime('today');

        return TutorSchedule::where('tutor_id', $tutorId)->where('start_time', '>=', $today)->count();
    }
}

/**
 * Count booked schedules for tutor from today.
 *
 * @param  int|string|null  $tutorId
 * @return int
 */
if (! function_exists('total_booked_schedule_by_tutor_id')) {
    function total_booked_schedule_by_tutor_id($tutorId = null)
    {
        $today = strtotime('today');

        return TutorBooking::where('tutor_id', $tutorId)->where('start_time', '>=', $today)->count();
    }
}

/**
 * Count reviews for a tutor.
 *
 * @param  int|string|null  $tutorId
 * @return int
 */
if (! function_exists('total_review_by_tutor_id')) {
    function total_review_by_tutor_id($tutorId = null)
    {
        return TutorReview::where('tutor_id', $tutorId)->count();
    }
}

/**
 * Count booked seats for a specific schedule.
 *
 * @param  int|string|null  $scheduleId
 * @return int
 */
if (! function_exists('total_booked_schedule_by_schedule_id')) {
    function total_booked_schedule_by_schedule_id($scheduleId = null)
    {
        return TutorBooking::where('schedule_id', $scheduleId)->count();
    }
}

/**
 * Is a tuition class currently joinable?
 *
 * @param  int|string  $bookingId
 * @return bool|null
 */
if (! function_exists('tution_started')) {
    function tution_started($bookingId)
    {
        $now = time();
        $window = $now + (60 * 15);

        $row = TutorBooking::whereKey($bookingId)
            ->whereNotNull('joining_data')
            ->where('start_time', '<', $window)
            ->where('end_time', '>', $now)
            ->first();

        return $row ? true : null;
    }
}

/**
 * Enrollment status summary (valid/expired/false) for a user's course.
 *
 * @param  int|string  $courseId
 * @param  int|string  $userId
 * @return 'valid'|'expired'|false|null
 */
if (! function_exists('enroll_status')) {
    function enroll_status($courseId = '', $userId = '')
    {
        if ($courseId === '' || $userId === '') {
            return null;
        }

        $enrolled = Enrollment::where('course_id', $courseId)->where('user_id', $userId)->first();
        if (! $enrolled) {
            return false;
        }

        $expiry = $enrolled->expiry_date;
        if ($expiry === null || (int) $expiry >= time()) {
            return 'valid';
        }

        return 'expired';
    }
}

/**
 * Convert seconds to "HH:MM:SS".
 *
 * @param  int|string  $seconds
 * @return string
 */
if (! function_exists('seconds_to_time_format')) {
    function seconds_to_time_format($seconds = '0')
    {
        $seconds = (int) $seconds;
        $h = floor($seconds / 3600);
        $m = floor(($seconds % 3600) / 60);
        $s = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $h, $m, $s);
    }
}

/**
 * Aggressive JS removal (alternative to removeScripts) with optional escape/convert.
 *
 * @param  string  $description
 * @param  bool  $convert
 * @return string
 */
if (! function_exists('remove_js')) {
    function remove_js($description = '', $convert = false)
    {
        if ($convert) {
            return nl2br(htmlspecialchars($description));
        }

        $description = str_replace(['&lt;script&gt;', '&lt;/script&gt;'], '', $description);
        $description = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $description);
        $description = preg_replace("/[<][^<]*script.*[>].*[<].*[\/].*script*[>]/i", '', $description);
        $description = preg_replace("/([ ]on[a-zA-Z0-9_-]{1,}=\".*\")|([ ]on[a-zA-Z0-9_-]{1,}='.*')|([ ]on[a-zA-Z0-9_-]{1,}=.*[.].*)/", '', $description);
        $description = preg_replace('/(<.+?)(?<=\s)on[a-z]+\s*=\s*(?:([\'"])(?!\2).+?\2|(?:\S+?\(.*?\)(?=[\s>])))(.*?>)/i', '$1 $3', $description);
        $description = preg_replace("/([ ]href.*=\".*javascript:.*\")|([ ]href.*='.*javascript:.*')|([ ]href.*=.*javascript:.*)/i", '', $description);

        return $description;
    }
}

/**
 * Compute locked lesson IDs under drip logic, skipping allowed next lesson.
 *
 * @param  int|string  $courseId
 * @param  int|string  $userId
 * @return array<int>
 */
if (! function_exists('get_locked_lesson_ids')) {
    function get_locked_lesson_ids($courseId, $userId)
    {
        $locked = [];

        // Admin-instructor bypass
        if (check_course_admin($userId) === 'admin' && is_course_instructor($courseId, $userId)) {
            return $locked;
        }

        $sections = Section::where('course_id', $courseId)->orderBy('sort')->get();
        $history = WatchHistory::where('course_id', $courseId)->where('student_id', $userId)->first();
        $completed = $history ? (json_decode($history->completed_lesson, true) ?: []) : [];
        $lastCompleted = end($completed) ?: null;

        foreach ($sections as $sIndex => $section) {
            $lessons = Lesson::where('section_id', $section->id)->orderBy('sort')->get();
            foreach ($lessons as $lIndex => $lesson) {
                // Skip first lesson of first section
                if ($sIndex === 0 && $lIndex === 0) {
                    continue;
                }
                // Allow next lesson after last completed
                if ($lastCompleted && $lesson->id === next_lesson($courseId, $lastCompleted)) {
                    continue;
                }
                if (! in_array($lesson->id, $completed, true)) {
                    $locked[] = $lesson->id;
                }
            }
        }

        return $locked;
    }
}

/**
 * Get watched duration payload for a lesson/user (raw JSON string for parity).
 *
 * @param  int|string  $lessonId
 * @param  int|string  $userId
 * @return string|null
 */
if (! function_exists('get_watched_duration')) {
    function get_watched_duration($lessonId, $userId)
    {
        $row = \App\Models\WatchDuration::where('watched_lesson_id', $lessonId)
            ->where('watched_student_id', $userId)
            ->first();

        return $row ? json_encode($row) : null;
    }
}

/**
 * Get the next lesson ID for a course respecting sections & sort order.
 *
 * @param  int|string  $courseId
 * @param  int|string  $lessonId
 * @return int|null
 */
if (! function_exists('next_lesson')) {
    function next_lesson($courseId = '', $lessonId = '')
    {
        $list = Lesson::join('sections', 'lessons.section_id', '=', 'sections.id')
            ->where('lessons.course_id', $courseId)
            ->orderBy('sections.sort')
            ->orderBy('lessons.sort')
            ->pluck('lessons.id')
            ->toArray();

        $idx = array_search((int) $lessonId, array_map('intval', $list), true);
        if ($idx !== false && isset($list[$idx + 1])) {
            return (int) $list[$idx + 1];
        }

        return null;
    }
}

/**
 * Verify Google reCAPTCHA v2/v3 token via server call.
 *
 * @param  string  $token
 * @return bool
 */
if (! function_exists('check_recaptcha')) {
    function check_recaptcha($token = '')
    {
        if (! $token) {
            return false;
        }

        $secret = get_frontend_settings('recaptcha_secretkey');
        if (! $secret) {
            return false;
        }

        $params = http_build_query(['secret' => $secret, 'response' => $token]);
        $opts = ['http' => [
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n"
                       .'Content-Length: '.strlen($params)."\r\n"
                       ."User-Agent: Laravel/Helper\r\n",
            'method' => 'POST',
            'content' => $params,
            'timeout' => 5,
        ]];
        $context = stream_context_create($opts);
        $resp = @file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);

        if (! $resp) {
            return false;
        }

        $json = json_decode($resp);

        return (bool) ($json->success ?? false);
    }
}
