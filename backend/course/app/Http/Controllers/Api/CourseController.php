<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Language;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function top_courses()
    {
        $courses = Course::orderBy('id', 'desc')->where('status', 'active')->limit(10)->get();

        return course_data($courses);
    }

    public function courses_by_search_string(Request $request)
    {
        $search_string = $request->search_string;
        $courses = Course::where('title', 'LIKE', "%{$search_string}%")
            ->where('status', 'active')->get();

        return course_data($courses);
    }

    public function filter_course(Request $request)
    {
        $query = Course::query();

        if ($request->filled('selected_search_string')) {
            $query->where('title', 'LIKE', '%'.$request->selected_search_string.'%');
        }

        if ($request->selected_category !== 'all') {
            $query->where('category_id', $request->selected_category);
        }

        if ($request->selected_price !== 'all') {
            if ($request->selected_price === 'paid') {
                $query->where('is_paid', 1);
            } elseif ($request->selected_price === 'free') {
                $query->where(function ($q) {
                    $q->where('is_paid', 0)->orWhereNull('is_paid');
                });
            }
        }

        if ($request->selected_level !== 'all') {
            $query->where('level', $request->selected_level);
        }

        if ($request->selected_language !== 'all') {
            $query->where('language', $request->selected_language);
        }

        $query->where('status', 'active');
        $courses = $query->get();

        return course_data($courses);
    }

    public function category_wise_course(Request $request)
    {
        $courses = get_category_wise_courses($request->category_id);

        return course_data($courses);
    }

    public function category_subcategory_wise_course(Request $request)
    {
        $courses = get_category_wise_courses($request->category_id);

        return course_data($courses);
    }

    public function course_details_by_id(Request $request)
    {
        $course_id = $request->course_id;
        $user_id = auth('sanctum')->check() ? auth('sanctum')->user()->id : 0;

        return course_details_by_id($user_id, $course_id);
    }

    public function sections(Request $request)
    {
        $user = $request->user();
        $course_id = $request->course_id;

        return sections($course_id, $user->id);
    }

    public function languages()
    {
        $languages = Language::select('name')->distinct()->get();
        $response = [];

        foreach ($languages as $index => $language) {
            $response[] = [
                'id' => $index + 1,
                'value' => $language->name,
                'displayedValue' => ucfirst($language->name),
            ];
        }

        return $response;
    }

    public function free_course_enroll(Request $request, $course_id)
    {
        $user_id = $request->user()->id;
        $alreadyEnrolled = Enrollment::where('course_id', $course_id)
            ->where('user_id', $user_id)
            ->exists();

        if ($alreadyEnrolled) {
            return response()->json([
                'status' => false,
                'message' => 'Already enrolled in this course.',
            ]);
        }

        $done = Enrollment::insert([
            'user_id' => $user_id,
            'course_id' => $course_id,
            'enrollment_type' => 'free',
            'entry_date' => time(),
            'expiry_date' => null,
        ]);

        return response()->json([
            'status' => $done ? true : false,
            'message' => $done ? 'Course successfully enrolled.' : 'Enrollment failed. Try again.',
        ]);
    }
}
