<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class BlogController extends Controller
{
    public function index(Request $request, $category = '')
    {
        $query = Blog::query();

        if (! empty($category)) {
            $category_row = BlogCategory::where('slug', $category)->first();
            if ($category_row) {
                $query->where('category_id', $category_row->id);
            }
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%$search%")
                    ->orWhere('description', 'LIKE', "%$search%");
            });
        }

        $page_data['blogs'] = $query->latest('id')->paginate(6)->appends($request->query());

        $view = 'frontend.'.get_frontend_settings('theme').'.blog.index';

        return view($view, $page_data);
    }

    public function blog_details($slug)
    {
        $blog = Blog::with('user')
            ->where('slug', $slug)
            ->first();

        if (! $blog) {
            Session::flash('error', get_phrase('Data not found.'));

            return redirect()->back();
        }

        $page_data['blog_details'] = $blog;

        $page_data['blog_comments'] = BlogComment::with('user')
            ->where('blog_id', $blog->id)
            ->whereNull('parent_id')
            ->latest()
            ->get();

        $view = 'frontend.'.get_frontend_settings('theme').'.blog.details';

        return view($view, $page_data);
    }

    public function blog_by_category($id)
    {
        $category = BlogCategory::findOrFail($id);

        $page_data['blogs'] = Blog::where('category_id', $category->id)->latest()->paginate(6);

        $view = 'frontend.'.get_frontend_settings('theme').'.blog.index';

        return view($view, $page_data);
    }
}
