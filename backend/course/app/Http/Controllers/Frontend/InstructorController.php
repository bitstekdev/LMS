<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Facades\Session;

class InstructorController extends Controller
{
    public function index()
    {
        $page_data['instructors'] = User::where('role', 'instructor')
            ->latest('id')
            ->paginate(8);

        $view_path = 'frontend.'.get_frontend_settings('theme').'.instructor.index';

        return view($view_path, $page_data);
    }

    public function show($name, $id)
    {
        $instructor = User::where('id', $id)->where('role', 'instructor')->first();

        if (! $instructor || $instructor->name !== $name) {
            Session::flash('error', get_phrase('Data not found.'));

            return redirect()->back();
        }

        $page_data['instructor_details'] = $instructor;

        $page_data['instructor_courses'] = Course::where('user_id', $id)
            ->where('status', 'active')
            ->latest('id')
            ->paginate(6);

        $view_path = 'frontend.'.get_frontend_settings('theme').'.instructor.details';

        return view($view_path, $page_data);
    }
}
