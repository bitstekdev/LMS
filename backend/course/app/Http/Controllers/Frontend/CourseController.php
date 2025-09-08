<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Section;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CourseController extends Controller
{
    public function index(Request $request, $category = '')
    {
        $layout = Session::get('view', 'grid');
        $page_data['layout'] = $layout;

        $query = Course::with('user')
            ->where('status', 'active');

        if (! empty($category)) {
            $category_details = Category::where('slug', $category)->first();

            if ($category_details) {
                if ($category_details->parent_id > 0) {
                    $query->where('category_id', $category_details->id);
                    $page_data['child_cat'] = $category;
                } else {
                    $subCatIds = Category::where('parent_id', $category_details->id)->pluck('id')->toArray();
                    $subCatIds[] = $category_details->id;
                    $query->whereIn('category_id', $subCatIds);
                    $page_data['parent_cat'] = $category;
                }
                $page_data['category_details'] = $category_details;
            }
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%$search%")
                    ->orWhere('short_description', 'LIKE', "%$search%")
                    ->orWhere('level', 'LIKE', "%$search%")
                    ->orWhere('meta_keywords', 'LIKE', "%$search%")
                    ->orWhere('meta_description', 'LIKE', "%$search%")
                    ->orWhere('description', 'LIKE', "%$search%");
            });
        }

        if ($request->has('price')) {
            $price = $request->query('price');
            $query->when($price === 'paid', fn ($q) => $q->where('is_paid', 1))
                ->when($price === 'discount', fn ($q) => $q->where('discount_flag', 1))
                ->when($price === 'free', fn ($q) => $q->where('is_paid', 0));
        }

        if ($request->has('level')) {
            $query->where('level', $request->query('level'));
        }

        if ($request->has('language')) {
            $query->where('language', $request->query('language'));
        }

        if ($request->has('rating')) {
            $query->where('average_rating', $request->query('rating'));
        }

        $wishlist = auth('web')->check()
            ? Wishlist::where('user_id', auth('web')->id())->pluck('course_id')->toArray()
            : [];

        $page_data['courses'] = $query->latest()->paginate($layout === 'grid' ? 9 : 5)->appends($request->query());
        $page_data['wishlist'] = $wishlist;

        $view = 'frontend.'.get_frontend_settings('theme').'.course.index';

        return view($view, $page_data);
    }

    public function course_details(Request $request, $slug)
    {
        if (empty($slug)) {
            return redirect()->back();
        }

        $course = Course::where('slug', $slug)->where('status', 'active')->first();

        if (! $course) {
            return redirect()->back();
        }

        $page_data['course_details'] = $course;
        $page_data['sections'] = Section::where('course_id', $course->id)->orderBy('sort')->get();
        $page_data['total_lesson'] = Lesson::where('course_id', $course->id)->count();
        $page_data['enroll'] = Enrollment::where('course_id', $course->id)->count('user_id');

        $view = 'frontend.'.get_frontend_settings('theme').'.course.course_details';

        return view($view, $page_data);
    }

    public function change_layout(Request $request)
    {
        session(['view' => $request->view]);

        return response()->json(['reload' => true]);
    }
}
