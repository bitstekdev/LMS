<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\FrontendSetting;
use App\Models\SeoField;
use App\Services\FileUploaderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class BlogController extends Controller
{
    public function __construct(private FileUploaderService $uploader) {}

    public function index(Request $request)
    {
        $query = Blog::query();

        if ($search = $request->query('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        return view('admin.blog.index', [
            'blogs' => $query->paginate(10),
        ]);
    }

    public function create()
    {
        return view('admin.blog.create', [
            'category' => BlogCategory::all(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|unique:blogs,title',
            'category_id' => 'required|exists:blog_categories,id',
            'description' => 'required',
        ]);

        $data = [
            'category_id' => $request->category_id,
            'user_id' => auth('web')->id(),
            'title' => $request->title,
            'slug' => slugify($request->title),
            'keywords' => $request->keywords,
            'description' => $request->description,
            'is_popular' => $request->boolean('is_popular'),
            'status' => 1,
        ];

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $this->uploader->upload(
                $request->file('thumbnail'),
                'uploads/blog/thumbnail',
                400,
                null,
                200,
                200
            );
        }

        if ($request->hasFile('banner')) {
            $data['banner'] = $this->uploader->upload(
                $request->file('banner'),
                'uploads/blog/banner',
                1400,
                null,
                200,
                200
            );
        }

        Blog::create($data);

        return redirect()->route('admin.blogs')->with('success', get_phrase('Blog added successfully'));
    }

    public function edit($id)
    {
        $blog = Blog::findOrFail($id);

        return view('admin.blog.edit', [
            'blog_data' => $blog,
            'category' => BlogCategory::all(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $blog = Blog::findOrFail($id);

        $request->validate([
            'title' => 'required',
            'category_id' => 'required|exists:blog_categories,id',
            'description' => 'required',
        ]);

        $data = [
            'category_id' => $request->category_id,
            'title' => $request->title,
            'slug' => slugify($request->title),
            'keywords' => $request->keywords,
            'description' => $request->description,
            'is_popular' => $request->boolean('is_popular'),
        ];

        if ($request->hasFile('thumbnail')) {
            remove_file($blog->thumbnail);
            $data['thumbnail'] = $this->uploader->upload(
                $request->file('thumbnail'),
                'uploads/blog/thumbnail',
                400,
                null,
                200,
                200
            );
        }

        if ($request->hasFile('banner')) {
            remove_file($blog->banner);
            $data['banner'] = $this->uploader->upload(
                $request->file('banner'),
                'uploads/blog/banner',
                1400,
                null,
                200,
                200
            );
        }

        $blog->update($data);

        // Handle SEO
        $seo = SeoField::firstOrNew([
            'name_route' => 'blog.details',
            'blog_id' => $blog->id,
        ]);

        $meta_keywords = collect(json_decode($request->meta_keywords, true))
            ->pluck('value')
            ->implode(', ');

        $seo->fill([
            'route' => 'Blog Details',
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'meta_robot' => $request->meta_robot,
            'canonical_url' => $request->canonical_url,
            'custom_url' => $request->custom_url,
            'json_ld' => $request->json_ld,
            'og_title' => $request->og_title,
            'og_description' => $request->og_description,
            'meta_keywords' => $meta_keywords,
        ]);

        if ($request->hasFile('og_image')) {
            remove_file($seo->og_image);
            $seo->og_image = $this->uploader->upload(
                $request->file('og_image'),
                'uploads/seo-og-images',
                600
            );
        }

        $seo->save();

        return redirect()->route('admin.blogs')->with('success', get_phrase('Blog updated successfully'));
    }

    public function delete($id)
    {
        $blog = Blog::findOrFail($id);

        remove_file($blog->thumbnail);
        remove_file($blog->banner);

        $blog->delete();

        Session::flash('success', get_phrase('Blog deleted successfully'));

        return redirect()->back();
    }

    public function status($id)
    {
        $blog = Blog::findOrFail($id);

        $blog->update([
            'status' => ! $blog->status,
        ]);

        return response()->json([
            'success' => get_phrase('Status has been updated.'),
        ]);
    }

    public function pending(Request $request)
    {
        $query = Blog::where('status', 0);

        if ($search = $request->query('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        return view('admin.blog.pending', [
            'blogs' => $query->paginate(10),
        ]);
    }

    public function settings()
    {
        return view('admin.blog.setting');
    }

    public function update_settings(Request $request)
    {
        FrontendSetting::updateOrInsert(
            ['key' => 'instructors_blog_permission'],
            ['value' => $request->instructors_blog_permission]
        );

        FrontendSetting::updateOrInsert(
            ['key' => 'blog_visibility_on_the_home_page'],
            ['value' => $request->blog_visibility_on_the_home_page]
        );

        return redirect()->back()->with('success', get_phrase('Settings updated successfully'));
    }
}
