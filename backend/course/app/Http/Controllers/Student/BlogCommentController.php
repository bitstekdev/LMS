<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\BlogComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class BlogCommentController extends Controller
{
    /**
     * Store a new blog comment.
     */
    public function store(Request $request)
    {
        $request->validate([
            'comment' => 'required|string|min:1',
            'blog_id' => 'required|exists:blogs,id',
            'parent_id' => 'nullable|exists:blog_comments,id',
        ]);

        BlogComment::create([
            'user_id' => auth('web')->id(),
            'blog_id' => $request->blog_id,
            'comment' => $request->comment,
            'parent_id' => $request->parent_id ?? null,
        ]);

        Session::flash('success', get_phrase('Your comment has been saved.'));

        return redirect()->back();
    }

    /**
     * Delete a blog comment.
     */
    public function delete($id)
    {
        $comment = BlogComment::where('id', $id)->where('user_id', auth('web')->id())->first();

        if (! $comment) {
            Session::flash('error', get_phrase('Comment not found or unauthorized.'));

            return redirect()->back();
        }

        $comment->delete();

        Session::flash('success', get_phrase('Your comment has been deleted.'));

        return redirect()->back();
    }

    /**
     * Update a blog comment.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|string|min:1',
        ]);

        $comment = BlogComment::where('id', $id)->where('user_id', auth('web')->id())->first();

        if (! $comment) {
            Session::flash('error', get_phrase('Comment not found or unauthorized.'));

            return redirect()->back();
        }

        $comment->update([
            'comment' => $request->comment,
        ]);

        Session::flash('success', get_phrase('Your comment has been updated.'));

        return redirect()->back();
    }
}
