<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\FileUploaderService;
use Illuminate\Http\Request;

// keep if your project uses this helper-style uploader

class CategoryController extends Controller
{
    /**
     * List top-level categories.
     */
    public function index()
    {
        $page_data['categories'] = Category::with('childs')
            ->where('parent_id', 0)
            ->orderBy('sort', 'asc')
            ->get();

        return view('admin.category.index', $page_data);
    }

    /**
     * (Optional) Show create form.
     * Keep empty if using a modal/inline form on index.
     */
    public function create()
    {
        return view('admin.category.create');
    }

    /**
     * Store a new category.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'parent_id' => 'required|integer|min:0',
            'icon' => 'required|string',
            'keywords' => 'nullable|string|max:400',
            'description' => 'nullable|string|max:500',
            'thumbnail' => 'nullable|image|max:3072',
            'category_logo' => 'nullable|image|max:3072',
        ]);

        $slug = slugify($request->title);

        // ensure unique slug
        if (Category::where('slug', $slug)->exists()) {
            return redirect()->route('admin.categories')
                ->with('error', get_phrase('There cannot be more than one category with the same name. Please change your category name'));
        }

        $data = [
            'parent_id' => (int) $request->parent_id ?? null,
            'title' => $request->title,
            'slug' => $slug,
            'icon' => $request->icon,
            'sort' => 0,
            'keywords' => $request->keywords,
            'description' => $request->description,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // thumbnail upload
        if ($request->hasFile('thumbnail') && $request->file('thumbnail')->isValid()) {
            $data['thumbnail'] = 'uploads/category-thumbnail/'.nice_file_name($request->title, $request->file('thumbnail')->extension());
            // Resize to width 500, keep aspect; generate 200x200 thumb if your uploader supports last two args
            app(FileUploaderService::class)->upload($request->file('thumbnail'), $data['thumbnail'], 500, null, 200, 200);
        }

        // logo upload
        if ($request->hasFile('category_logo') && $request->file('category_logo')->isValid()) {
            $data['category_logo'] = 'uploads/category-logo/'.nice_file_name($request->title.' logo', $request->file('category_logo')->extension());
            app(FileUploaderService::class)->upload($request->file('category_logo'), $data['category_logo'], 400, null, 200, 200);
        }

        Category::insert($data);

        return redirect()->route('admin.categories')->with('success', get_phrase('Category added successfully'));
    }

    /**
     * (Optional) Show edit form.
     * Keep empty if using a modal/inline form on index.
     */
    public function edit()
    {
        return view('admin.category.edit');
    }

    /**
     * Update a category.
     */
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail((int) $id);

        $request->validate([
            'title' => 'required|string|max:255',
            'parent_id' => 'required|integer|min:0',
            'icon' => 'required|string',
            'keywords' => 'nullable|string|max:400',
            'description' => 'nullable|string|max:500',
            'thumbnail' => 'nullable|image|max:3072',
            'category_logo' => 'nullable|image|max:3072',
        ]);

        $newSlug = slugify($request->title);

        // ensure unique slug excluding current category
        $slugExists = Category::where('slug', $newSlug)->where('id', '!=', $category->id)->exists();
        if ($slugExists) {
            return redirect()->route('admin.categories')
                ->with('error', get_phrase('There cannot be more than one category with the same name. Please change your category name'));
        }

        $update = [
            'parent_id' => (int) $request->parent_id,
            'title' => $request->title,
            'slug' => $newSlug,
            'icon' => $request->icon,
            'keywords' => $request->keywords,
            'description' => $request->description,
            'updated_at' => now(),
        ];

        // thumbnail upload (replace old)
        if ($request->hasFile('thumbnail') && $request->file('thumbnail')->isValid()) {
            $update['thumbnail'] = 'uploads/category-thumbnail/'.nice_file_name($request->title, $request->file('thumbnail')->extension());
            app(FileUploaderService::class)->upload($request->file('thumbnail'), $update['thumbnail'], 500, null, 200, 200);
            if (! empty($category->thumbnail)) {
                remove_file($category->thumbnail);
            }
        }

        // logo upload (replace old)
        if ($request->hasFile('category_logo') && $request->file('category_logo')->isValid()) {
            $update['category_logo'] = 'uploads/category-logo/'.nice_file_name($request->title.'-logo', $request->file('category_logo')->extension());
            app(FileUploaderService::class)->upload($request->file('category_logo'), $update['category_logo'], 400, null, 200, 200);
            if (! empty($category->category_logo)) {
                remove_file($category->category_logo);
            }
        }

        $category->update($update);

        return redirect()->route('admin.categories')->with('success', get_phrase('Category updated successfully'));
    }

    /**
     * Delete a category (and its immediate children if top-level).
     */
    public function delete($id)
    {
        $category = Category::with('childs')->findOrFail((int) $id);

        // If top-level, remove all direct childs first
        if ((int) $category->parent_id === 0) {
            foreach ($category->childs as $child) {
                if (! empty($child->thumbnail)) {
                    remove_file($child->thumbnail);
                }
                if (! empty($child->category_logo)) {
                    remove_file($child->category_logo);
                }
                $child->delete();
            }
        }

        // Remove its own files
        if (! empty($category->thumbnail)) {
            remove_file($category->thumbnail);
        }
        if (! empty($category->category_logo)) {
            remove_file($category->category_logo);
        }

        $category->delete();

        return redirect()->route('admin.categories')->with('success', get_phrase('Category deleted successfully'));
    }
}
