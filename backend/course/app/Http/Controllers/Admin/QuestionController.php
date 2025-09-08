<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class QuestionController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'type' => 'required|in:mcq,fill_blanks,true_false',
            'answer' => 'required',
            'options' => 'required_if:type,mcq',
        ], [
            'options.required_if' => 'When type is MCQ, options are required.',
        ]);

        if ($validator->fails()) {
            return response()->json(['validationError' => $validator->getMessageBag()->toArray()]);
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
            return response()->json(['error' => get_phrase('Data not found.')]);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'type' => 'required|in:mcq,fill_blanks,true_false',
            'answer' => 'required',
            'options' => 'required_if:type,mcq',
        ], [
            'options.required_if' => 'When type is MCQ, options are required.',
        ]);

        if ($validator->fails()) {
            return response()->json(['validationError' => $validator->getMessageBag()->toArray()]);
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
        $questionIds = json_decode($request->itemJSON);
        foreach ($questionIds as $index => $id) {
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
            return response()->json(['error' => 'Invalid type'], 400);
        }

        $action = $request->id ? 'edit' : 'create';
        $view = "admin.questions.{$action}_{$types[$request->type]}";

        $page_data = [];
        if ($request->id) {
            $page_data['question'] = Question::find($request->id);
        }

        return view($view, $page_data);
    }

    private function prepareQuestionData(Request $request)
    {
        $answer = null;
        $options = null;

        switch ($request->type) {
            case 'mcq':
                $answer = json_encode($request->answer);
                $optionsArray = json_decode($request->options, true);
                $options = json_encode(array_column($optionsArray, 'value'));
                break;

            case 'fill_blanks':
                $answersArray = json_decode($request->answer, true);
                $answer = json_encode(array_column($answersArray, 'value'));
                break;

            case 'true_false':
                $answer = $request->answer;
                break;
        }

        return [
            'quiz_id' => $request->quiz_id,
            'title' => $request->title,
            'type' => $request->type,
            'answer' => $answer,
            'options' => $options,
        ];
    }
}
