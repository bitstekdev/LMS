<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BootcampCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class BootcampCategoryController extends Controller
{
    public function index()
    {
        $categories = BootcampCategory::paginate(32);

        return view('admin.bootcamp_category.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|unique:bootcamp_categories,title',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        BootcampCategory::create([
            'title' => $request->title,
            'slug' => slugify($request->title),
        ]);

        Session::flash('success', get_phrase('Category has been created.'));

        return back();
    }

    public function update(Request $request, $id)
    {
        $category = BootcampCategory::find($id);

        if (! $category) {
            Session::flash('error', get_phrase('Category not found.'));

            return back();
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|unique:bootcamp_categories,title,'.$id,
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $category->update([
            'title' => $request->title,
            'slug' => slugify($request->title),
        ]);

        Session::flash('success', get_phrase('Category has been updated.'));

        return back();
    }

    public function delete($id)
    {
        $category = BootcampCategory::find($id);

        if (! $category) {
            Session::flash('error', get_phrase('Category not found.'));

            return back();
        }

        $category->delete();

        Session::flash('success', get_phrase('Category has been deleted.'));

        return back();
    }
}
