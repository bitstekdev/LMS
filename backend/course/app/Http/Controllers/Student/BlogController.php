<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\BlogLike;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    /**
     * Toggle like/unlike for a blog post.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function blog_like(Request $request)
    {
        $request->validate([
            'blog_id' => 'required|integer|exists:blogs,id',
        ]);

        $userId = auth('web')->id();

        $existingLike = BlogLike::where('blog_id', $request->blog_id)
            ->where('user_id', $userId)
            ->first();

        if ($existingLike) {
            $existingLike->delete();

            return response()->json(['like' => false]);
        } else {
            BlogLike::create([
                'blog_id' => $request->blog_id,
                'user_id' => $userId,
            ]);

            return response()->json(['like' => true]);
        }
    }
}
