<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\QuizSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class QuizController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'section' => 'required|numeric',
            'hour' => 'nullable|numeric|max:23|min:0',
            'minute' => 'nullable|numeric|max:59|min:0',
            'second' => 'nullable|numeric|max:59|min:0',
            'total_mark' => 'required|numeric|min:1',
            'pass_mark' => 'required|numeric|min:1',
            'retake' => 'required|numeric|min:1',
        ])->after(function ($validator) use ($request) {
            $hour = (int) $request->hour;
            $minute = (int) $request->minute;
            $second = (int) $request->second;

            if ($hour == 0 && $minute == 0 && $second == 0) {
                $validator->errors()->add('second', 'If hour and minute are 0, second must be greater than 0.');
            }

            if ((int) $request->pass_mark > (int) $request->total_mark) {
                $validator->errors()->add('pass_mark', 'The pass mark must be less than or equal to the total mark.');
            }
        });

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $existingTitle = Lesson::join('sections', 'lessons.section_id', 'sections.id')
            ->join('courses', 'sections.course_id', 'courses.id')
            ->where('courses.user_id', auth('web')->id())
            ->where('lessons.title', $request->title)
            ->first();

        if ($existingTitle) {
            Session::flash('error', get_phrase('Title has been taken.'));

            return redirect()->back();
        }

        $data = [
            'title' => $request->title,
            'course_id' => $request->course_id,
            'section_id' => $request->section,
            'total_mark' => $request->total_mark,
            'pass_mark' => $request->pass_mark,
            'retake' => $request->retake,
            'description' => $request->description,
            'lesson_type' => 'quiz',
            'status' => 1,
            'duration' => sprintf('%02d:%02d:%02d', $request->hour ?? 0, $request->minute ?? 0, $request->second ?? 0),
        ];

        Lesson::create($data);

        Session::flash('success', get_phrase('Quiz has been created.'));

        return redirect()->back();
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'section' => 'required|numeric',
            'hour' => 'nullable|numeric|max:23|min:0',
            'minute' => 'nullable|numeric|max:59|min:0',
            'second' => 'nullable|numeric|max:59|min:0',
            'total_mark' => 'required|numeric|min:1',
            'pass_mark' => 'required|numeric|min:1',
            'retake' => 'required|numeric|min:1',
        ])->after(function ($validator) use ($request) {
            $hour = (int) $request->hour;
            $minute = (int) $request->minute;
            $second = (int) $request->second;

            if ($hour == 0 && $minute == 0 && $second == 0) {
                $validator->errors()->add('second', 'If hour and minute are 0, second must be greater than 0.');
            }

            if ((int) $request->pass_mark > (int) $request->total_mark) {
                $validator->errors()->add('pass_mark', 'The pass mark must be less than or equal to the total mark.');
            }
        });

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $duplicateTitle = Lesson::join('sections', 'lessons.section_id', 'sections.id')
            ->join('courses', 'sections.course_id', 'courses.id')
            ->where('lessons.id', '!=', $id)
            ->where('lessons.title', $request->title)
            ->where('courses.user_id', auth('web')->id())
            ->first();

        if ($duplicateTitle) {
            Session::flash('error', get_phrase('Title has been taken.'));

            return redirect()->back();
        }

        $data = [
            'title' => $request->title,
            'section_id' => $request->section,
            'total_mark' => $request->total_mark,
            'pass_mark' => $request->pass_mark,
            'retake' => $request->retake,
            'description' => $request->description,
            'lesson_type' => 'quiz',
            'status' => 1,
            'duration' => sprintf('%02d:%02d:%02d', $request->hour ?? 0, $request->minute ?? 0, $request->second ?? 0),
        ];

        Lesson::where('id', $id)->update($data);

        Session::flash('success', get_phrase('Quiz has been updated.'));

        return redirect()->back();
    }

    public function result(Request $request)
    {
        $submissions = QuizSubmission::where('quiz_id', $request->quizId)
            ->where('user_id', $request->participant)
            ->get();

        $options = ['<option>'.get_phrase('Select an option').'</option>'];
        foreach ($submissions as $index => $submission) {
            $options[] = "<option value='{$submission->id}'>Attempt ".($index + 1).'</option>';
        }

        return $options;
    }

    public function result_preview(Request $request)
    {
        $page_data = [
            'quiz' => Lesson::find($request->quizId),
            'results' => QuizSubmission::where('quiz_id', $request->quizId)
                ->where('user_id', $request->participantId)
                ->get(),
            'questions' => Question::where('quiz_id', $request->quizId)->get(),
        ];

        return view('admin.quiz_result.preview', $page_data);
    }
}
