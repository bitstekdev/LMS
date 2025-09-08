<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\QuizSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class QuizController extends Controller
{
    public function quiz_submit(Request $request)
    {
        $userId = auth('web')->id();

        $quiz = Lesson::with('questions')->findOrFail($request->quiz_id);

        $alreadySubmittedCount = QuizSubmission::where('quiz_id', $quiz->id)
            ->where('user_id', $userId)
            ->count();

        if ($alreadySubmittedCount >= $quiz->retake) {
            Session::flash('warning', get_phrase('Attempt has been over.'));

            return redirect()->back();
        }

        $submittedAnswers = collect($request->except(['_token', 'quiz_id']))
            ->filter();

        $processedAnswers = $submittedAnswers->map(function ($answer) {
            if (is_string($answer) && ! in_array($answer, ['true', 'false'])) {
                return array_column(json_decode($answer), 'value');
            }

            return $answer;
        });

        $questionIds = $processedAnswers->keys();
        $questions = Question::whereIn('id', $questionIds)->get()->keyBy('id');

        $right = [];
        $wrong = [];

        foreach ($processedAnswers as $questionId => $answer) {
            $question = $questions[$questionId] ?? null;
            if (! $question) {
                continue;
            }

            $correct = json_decode($question->answer, true);
            $submitted = $answer;

            $isCorrect = false;

            switch ($question->type) {
                case 'mcq':
                    $isCorrect = empty(array_diff($correct, $submitted)) && empty(array_diff($submitted, $correct));
                    break;
                case 'fill_blanks':
                    if (count($correct) === count($submitted)) {
                        $isCorrect = collect($correct)->every(fn ($ans, $i) => strtolower($ans) === strtolower($submitted[$i]));
                    }
                    break;
                case 'true_false':
                    $isCorrect = strtolower(json_encode($correct)) === strtolower($submitted);
                    break;
            }

            $isCorrect ? $right[] = $questionId : $wrong[] = $questionId;
        }

        QuizSubmission::create([
            'quiz_id' => $quiz->id,
            'user_id' => $userId,
            'correct_answer' => $right ? json_encode($right) : null,
            'wrong_answer' => $wrong ? json_encode($wrong) : null,
            'submits' => $processedAnswers->isNotEmpty() ? json_encode($processedAnswers) : null,
        ]);

        Session::flash('success', get_phrase('Your answers have been submitted.'));

        return redirect()->back();
    }

    public function load_result(Request $request)
    {
        $userId = auth('web')->id();

        $quiz = Lesson::with('questions')->findOrFail($request->quiz_id);
        $result = QuizSubmission::where('id', $request->submit_id)
            ->where('quiz_id', $quiz->id)
            ->where('user_id', $userId)
            ->firstOrFail();

        return view('course_player.quiz.result', [
            'quiz' => $quiz,
            'questions' => $quiz->questions,
            'result' => $result,
        ]);
    }

    public function load_questions(Request $request)
    {
        $userId = auth('web')->id();

        $quiz = Lesson::with(['questions', 'course'])->findOrFail($request->quiz_id);
        $submissions = QuizSubmission::where('quiz_id', $quiz->id)
            ->where('user_id', $userId)
            ->get();

        return view('course_player.quiz.questions', [
            'quiz' => $quiz,
            'questions' => $quiz->questions,
            'submits' => $submissions,
            'course_details' => $quiz->course,
        ]);
    }
}
