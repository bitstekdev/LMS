<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use Illuminate\Support\Facades\Auth;

class MyCoursesController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $my_courses = Enrollment::with([
            'course:id,title,slug,thumbnail,user_id',
            'course.instructor:id,name,photo',
        ])
            ->where('user_id', $userId)
            ->latest()
            ->get()
            ->unique('course_id')
            ->values();

        $view_path = 'frontend.'.get_frontend_settings('theme').'.student.my_courses.index';

        return view($view_path, compact('my_courses'));
    }
}
