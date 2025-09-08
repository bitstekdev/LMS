<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use App\Models\TeamTrainingPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class TeamTrainingController extends Controller
{
    // List all available team training packages, optionally filtered by course category slug
    public function index(Request $request, $course_category = '')
    {
        $query = TeamTrainingPackage::with(['course:id,title,slug,price', 'user:id,name,email,photo'])
            ->where('status', 1);

        // Search filter
        if ($request->filled('search')) {
            $query->where('title', 'LIKE', '%'.$request->search.'%');
        }

        // Category filter
        if (! empty($course_category)) {
            $category = Category::where('slug', $course_category)->first();

            if ($category) {
                $courseIds = [];

                if ($category->parent_id === 0) {
                    $subCatIds = Category::where('parent_id', $category->id)->pluck('id');
                    $courseIds = Course::whereIn('category_id', $subCatIds)->pluck('id');
                } else {
                    $courseIds = Course::where('category_id', $category->id)->pluck('id');
                }

                $query->whereIn('course_id', $courseIds);
            }
        }

        $page_data['packages'] = $query->latest('id')->paginate(5)->appends($request->query());

        return view('frontend.default.team_training.index', $page_data);
    }

    // Show details for a specific team training package by slug
    public function show($slug)
    {
        $package = TeamTrainingPackage::with(['course:id,title,slug,price', 'user:id,name,email,photo'])
            ->where('slug', $slug)
            ->first();

        if (! $package) {
            Session::flash('error', get_phrase('Data not found.'));

            return redirect()->back();
        }

        return view('frontend.default.team_training.details', [
            'package' => $package,
        ]);
    }
}
