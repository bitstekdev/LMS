<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\Controller;
use App\Models\Forum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ForumController extends Controller
{
    public function index(Request $request)
    {
        $questions = Forum::with('user')
            ->where('parent_id', 0)
            ->where('course_id', $request->course_id)
            ->latest()
            ->get();

        return view('course_player.forum.question_body', [
            'questions' => $questions,
        ]);
    }

    public function create(Request $request)
    {
        return view('course_player.forum.create_question', [
            'course_id' => $request->course_id,
            'parent_question_id' => $request->parent_question_id,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
        ]);

        $isReply = $request->title === 'reply';

        $forum = Forum::create([
            'user_id' => auth('web')->id(),
            'course_id' => $request->course_id,
            'parent_id' => $request->parent_id ?? 0,
            'title' => $request->title,
            'description' => $isReply ? strip_tags($request->description) : $request->description,
        ]);

        $message = $isReply ? 'Reply added successfully.' : 'Question added successfully.';
        Session::flash('success', get_phrase($message));

        return redirect()->back();
    }

    public function edit(Request $request)
    {
        $question = Forum::findOrFail($request->question_id);

        return view('course_player.forum.edit_question', [
            'question' => $question,
            'course_id' => $request->course_id,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
        ]);

        $forum = Forum::findOrFail($id);

        $isReply = $request->title === 'reply';

        $forum->update([
            'title' => $request->title,
            'description' => $isReply ? strip_tags($request->description) : $request->description,
        ]);

        $message = $isReply ? 'Reply updated successfully.' : 'Question updated successfully.';
        Session::flash('success', get_phrase($message));

        return redirect()->back();
    }

    public function delete($id)
    {
        $forum = Forum::where('id', $id)->where('user_id', auth('web')->id())->first();

        if (! $forum) {
            Session::flash('error', get_phrase('Data not found.'));
        } else {
            $forum->delete();
            Session::flash('success', get_phrase('Question deleted successfully.'));
        }

        return redirect()->back();
    }

    public function likes($id)
    {
        $forum = Forum::findOrFail($id);
        $userId = auth('web')->id();

        $likes = json_decode($forum->likes ?? '[]', true);
        $dislikes = json_decode($forum->dislikes ?? '[]', true);

        if (in_array($userId, $likes)) {
            $likes = $this->removeUserFromArray($likes, $userId);
            Session::flash('success', get_phrase('Your like has been removed.'));
        } else {
            $likes[] = $userId;
            Session::flash('success', get_phrase('Your like has been added.'));
        }

        if (in_array($userId, $dislikes)) {
            $dislikes = $this->removeUserFromArray($dislikes, $userId);
        }

        $forum->update([
            'likes' => count($likes) ? json_encode($likes) : null,
            'dislikes' => count($dislikes) ? json_encode($dislikes) : null,
        ]);

        return redirect()->back();
    }

    public function dislikes($id)
    {
        $forum = Forum::findOrFail($id);
        $userId = auth('web')->id();

        $likes = json_decode($forum->likes ?? '[]', true);
        $dislikes = json_decode($forum->dislikes ?? '[]', true);

        if (in_array($userId, $dislikes)) {
            $dislikes = $this->removeUserFromArray($dislikes, $userId);
        } else {
            $dislikes[] = $userId;
        }

        if (in_array($userId, $likes)) {
            $likes = $this->removeUserFromArray($likes, $userId);
        }

        $forum->update([
            'likes' => count($likes) ? json_encode($likes) : null,
            'dislikes' => count($dislikes) ? json_encode($dislikes) : null,
        ]);

        Session::flash('success', get_phrase('Your changes have been saved.'));

        return redirect()->back();
    }

    public function tab_active(Request $request)
    {
        if ($request->has('tab')) {
            $tab = str_replace('pills-', '', explode('#', $request->tab)[1] ?? '');
            Session::put('forum_tab', $tab);
        }
    }

    public function create_reply(Request $request)
    {
        return view('course_player.forum.create_reply', [
            'parent_question_id' => $request->parent_question_id,
        ]);
    }

    public function edit_reply(Request $request)
    {
        $reply = Forum::findOrFail($request->reply_id);

        return view('course_player.forum.edit_reply', [
            'reply' => $reply,
        ]);
    }

    private function removeUserFromArray(array $array, $userId): array
    {
        return array_values(array_filter($array, fn ($id) => $id != $userId));
    }
}
