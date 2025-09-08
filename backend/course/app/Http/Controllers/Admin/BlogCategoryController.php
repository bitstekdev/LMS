<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class BlogCategoryController extends Controller
{
    public function index()
    {
        $categories = BlogCategory::all();

        return view('admin.blog_category.index', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
        ]);

        BlogCategory::create([
            'title' => $request->title,
            'subtitle' => $request->subtitle,
            'slug' => slugify($request->title),
        ]);

        Session::flash('success', get_phrase('Category added successfully'));

        return redirect()->back();
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
        ]);

        $category = BlogCategory::findOrFail($id);

        $category->update([
            'title' => $request->title,
            'subtitle' => $request->subtitle,
            'slug' => slugify($request->title),
        ]);

        Session::flash('success', get_phrase('Category updated successfully'));

        return redirect()->back();
    }

    public function delete($id)
    {
        $category = BlogCategory::findOrFail($id);

        $category->delete();

        Session::flash('success', get_phrase('Category deleted successfully'));

        return redirect()->back();
    }
}
