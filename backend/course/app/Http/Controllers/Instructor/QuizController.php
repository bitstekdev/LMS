<?php

namespace App\Http\Controllers\Instructor;

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
        $this->validateQuiz($request);

        // Check for duplicate quiz title within the same user's courses
        $titleExists = Lesson::join('sections', 'lessons.section_id', '=', 'sections.id')
            ->join('courses', 'sections.course_id', '=', 'courses.id')
            ->where('courses.user_id', auth('web')->id())
            ->where('lessons.title', $request->title)
            ->exists();

        if ($titleExists) {
            Session::flash('error', get_phrase('Title has been taken.'));

            return back()->withInput();
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
            'duration' => $this->formatDuration($request),
        ];

        Lesson::create($data);

        Session::flash('success', get_phrase('Quiz has been created.'));

        return back();
    }

    public function update(Request $request, $id)
    {
        $this->validateQuiz($request, $id);

        // Check for duplicate quiz title within same instructor's courses, excluding current lesson
        $titleExists = Lesson::join('sections', 'lessons.section_id', '=', 'sections.id')
            ->join('courses', 'sections.course_id', '=', 'courses.id')
            ->where('lessons.id', '!=', $id)
            ->where('courses.user_id', auth('web')->id())
            ->where('lessons.title', $request->title)
            ->exists();

        if ($titleExists) {
            Session::flash('error', get_phrase('Title has been taken.'));

            return back()->withInput();
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
            'duration' => $this->formatDuration($request),
        ];

        Lesson::where('id', $id)->update($data);

        Session::flash('success', get_phrase('Quiz has been updated.'));

        return back();
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
        return view('instructor.quiz_result.preview', [
            'quiz' => Lesson::find($request->quizId),
            'results' => QuizSubmission::where('quiz_id', $request->quizId)
                ->where('user_id', $request->participantId)
                ->get(),
            'questions' => Question::where('quiz_id', $request->quizId)->get(),
        ]);
    }

    protected function validateQuiz(Request $request, $lessonId = null)
    {
        Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'section' => 'required|numeric|exists:sections,id',
            'hour' => 'nullable|numeric|min:0|max:23',
            'minute' => 'nullable|numeric|min:0|max:59',
            'second' => 'nullable|numeric|min:0|max:59',
            'total_mark' => 'required|numeric|min:1',
            'pass_mark' => 'required|numeric|min:0',
            'retake' => 'required|numeric|min:1',
        ])->after(function ($validator) use ($request) {
            $h = (int) $request->hour;
            $m = (int) $request->minute;
            $s = (int) $request->second;

            if ($h + $m + $s === 0) {
                $validator->errors()->add('duration', 'Duration must be greater than 0.');
            }

            if ($request->pass_mark > $request->total_mark) {
                $validator->errors()->add('pass_mark', 'Pass mark must not exceed total mark.');
            }
        })->validate();
    }

    protected function formatDuration(Request $request): string
    {
        $h = str_pad($request->hour ?? 0, 2, '0', STR_PAD_LEFT);
        $m = str_pad($request->minute ?? 0, 2, '0', STR_PAD_LEFT);
        $s = str_pad($request->second ?? 0, 2, '0', STR_PAD_LEFT);

        return "$h:$m:$s";
    }
}
