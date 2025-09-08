<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Services\FileUploaderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BecomeInstructorController extends Controller
{
    public function index()
    {
        $view_path = 'frontend.'.get_frontend_settings('theme').'.student.become_instructor.index';

        return view($view_path);
    }

    public function store(Request $request)
    {
        $userId = auth('web')->id();

        // Check if an application already exists
        if (Application::where('user_id', $userId)->exists()) {
            Session::flash('error', get_phrase('Your request is in process. Please wait for admin to respond.'));

            return redirect()->route('become.instructor');
        }

        // Validation rules
        $rules = [
            'phone' => 'required',
            'document' => 'required|file|mimes:doc,docx,pdf,txt,png,jpg,jpeg|max:5120',
            'description' => 'required|string|min:10',
        ];

        // Validate request
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Handle file upload
        $document = $request->file('document');
        $fileName = 'uploads/applications/'.$userId.'_'.Str::random(20).'.'.$document->getClientOriginalExtension();
        app(FileUploaderService::class)->upload($document, $fileName);

        // Create application
        Application::create([
            'user_id' => $userId,
            'phone' => $request->phone,
            'description' => $request->description,
            'document' => $fileName,
        ]);

        Session::flash('success', get_phrase('Your application has been submitted.'));

        return redirect()->route('become.instructor');
    }
}
