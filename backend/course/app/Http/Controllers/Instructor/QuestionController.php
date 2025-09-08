<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class QuestionController extends Controller
{
    public function store(Request $request)
    {
        $validator = $this->validateQuestion($request);

        if ($validator->fails()) {
            return response()->json([
                'validationError' => $validator->errors()->toArray(),
            ]);
        }

        $data = $this->prepareQuestionData($request);

        Question::create($data);

        return response()->json([
            'status' => true,
            'success' => get_phrase('Question has been added.'),
            'functionCall' => 'responseBack()',
        ]);
    }

    public function update(Request $request, $id)
    {
        $question = Question::find($id);

        if (! $question) {
            return response()->json([
                'error' => get_phrase('Data not found.'),
            ]);
        }

        $validator = $this->validateQuestion($request);

        if ($validator->fails()) {
            return response()->json([
                'validationError' => $validator->errors()->toArray(),
            ]);
        }

        $data = $this->prepareQuestionData($request);

        $question->update($data);

        return response()->json([
            'status' => true,
            'success' => get_phrase('Question has been updated.'),
            'functionCall' => 'responseBack()',
        ]);
    }

    public function delete($id)
    {
        $question = Question::find($id);

        if (! $question) {
            Session::flash('error', get_phrase('Data not found.'));

            return redirect()->back();
        }

        $question->delete();

        Session::flash('success', get_phrase('Question has been deleted.'));

        return redirect()->back();
    }

    public function sort(Request $request)
    {
        $questions = json_decode($request->itemJSON);

        foreach ($questions as $index => $id) {
            Question::where('id', $id)->update(['sort' => $index + 1]);
        }

        Session::flash('success', get_phrase('Questions have been sorted.'));
    }

    public function load_type(Request $request)
    {
        $types = [
            'mcq' => 'mcq',
            'fill_blanks' => 'fill_blanks',
            'true_false' => 'true_false',
        ];

        if (! isset($types[$request->type])) {
            abort(404);
        }

        $action = $request->id ? 'edit' : 'create';
        $view = "admin.questions.{$action}_{$types[$request->type]}";
        $page_data = [];

        if ($request->id) {
            $page_data['question'] = Question::find($request->id);
        }

        return view($view, $page_data);
    }

    // --- ✅ Helper Methods ---

    protected function validateQuestion(Request $request)
    {
        return Validator::make($request->all(), [
            'title' => 'required|string|max:1000',
            'type' => 'required|in:mcq,fill_blanks,true_false',
            'answer' => 'required',
            'options' => 'required_if:type,mcq',
        ], [
            'options.required_if' => 'When type is MCQ, options are required.',
        ]);
    }

    protected function prepareQuestionData(Request $request)
    {
        $data = [
            'quiz_id' => $request->quiz_id,
            'title' => $request->title,
            'type' => $request->type,
        ];

        if ($request->type === 'mcq') {
            $data['answer'] = json_encode($request->answer);
            $data['options'] = json_encode(array_column(json_decode($request->options, true), 'value'));
        } elseif ($request->type === 'fill_blanks') {
            $answers = json_decode($request->answer);
            $data['answer'] = json_encode(array_column($answers, 'value'));
            $data['options'] = null;
        } elseif ($request->type === 'true_false') {
            $data['answer'] = $request->answer;
            $data['options'] = null;
        }

        return $data;
    }
}
