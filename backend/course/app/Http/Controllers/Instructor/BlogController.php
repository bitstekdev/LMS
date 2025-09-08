<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\SeoField;
use App\Services\FileUploaderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class BlogController extends Controller
{
    public function index()
    {
        $query = Blog::where('user_id', auth('web')->id());

        if (request()->has('search')) {
            $query->where('title', 'LIKE', '%'.request('search').'%');
        }

        $page_data['blogs'] = $query->paginate(10)->appends(request()->query());

        return view('instructor.blog.index', $page_data);
    }

    public function pending()
    {
        $query = Blog::where('user_id', auth('web')->id())->where('status', 0);

        if (request()->has('search')) {
            $query->where('title', 'LIKE', '%'.request('search').'%');
        }

        $page_data['blogs'] = $query->paginate(10)->appends(request()->query());

        return view('instructor.blog.pending', $page_data);
    }

    public function create()
    {
        $page_data['category'] = BlogCategory::all();

        return view('instructor.blog.create', $page_data);
    }

    public function store(Request $request)
    {
        $data = [
            'category_id' => $request->category_id,
            'user_id' => auth('web')->id(),
            'title' => $request->title,
            'slug' => slugify($request->title),
            'keywords' => $request->keywords,
            'description' => $request->description,
            'is_popular' => $request->is_popular,
            'status' => 0,
        ];

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = 'uploads/blog/thumbnail/'.nice_file_name($request->title, $request->thumbnail->extension());
            app(FileUploaderService::class)->upload($request->thumbnail, $data['thumbnail'], 400, null, 200, 200);
        }

        if ($request->hasFile('banner')) {
            $data['banner'] = 'uploads/blog/banner/'.nice_file_name($request->title, $request->banner->extension());
            app(FileUploaderService::class)->upload($request->banner, $data['banner'], 400, null, 200, 200);
        }

        Blog::create($data);

        Session::flash('success', get_phrase('Blog added successfully'));

        return redirect()->route('instructor.blogs');
    }

    public function edit($id)
    {
        $page_data['blog_data'] = Blog::where('id', $id)->where('user_id', auth('web')->id())->firstOrFail();
        $page_data['category'] = BlogCategory::all();

        return view('instructor.blog.edit', $page_data);
    }

    public function update(Request $request, $id)
    {
        $blog = Blog::where('id', $id)->where('user_id', auth('web')->id())->firstOrFail();

        $data = [
            'category_id' => $request->category_id,
            'user_id' => auth('web')->id(),
            'title' => $request->title,
            'slug' => slugify($request->title),
            'keywords' => $request->keywords,
            'description' => $request->description,
            'is_popular' => $request->is_popular,
        ];

        if ($request->hasFile('thumbnail')) {
            remove_file($blog->thumbnail);
            $data['thumbnail'] = 'uploads/blog/thumbnail/'.nice_file_name($request->title, $request->thumbnail->extension());
            app(FileUploaderService::class)->upload($request->thumbnail, $data['thumbnail'], 400, null, 200, 200);
        }

        if ($request->hasFile('banner')) {
            remove_file($blog->banner);
            $data['banner'] = 'uploads/blog/banner/'.nice_file_name($request->title, $request->banner->extension());
            app(FileUploaderService::class)->upload($request->banner, $data['banner'], 400, null, 200, 200);
        }

        $blog->update($data);

        // --- Blog SEO ---
        $this->updateSeo($request, $blog->id);

        Session::flash('success', get_phrase('Blog updated successfully'));

        return redirect()->route('instructor.blogs');
    }

    public function delete($id)
    {
        $blog = Blog::where('id', $id)->where('user_id', auth('web')->id())->firstOrFail();

        remove_file($blog->thumbnail);
        remove_file($blog->banner);

        $blog->delete();

        Session::flash('success', get_phrase('Blog deleted successfully'));

        return redirect()->back();
    }

    // --- ✅ Helper Method for SEO ---
    protected function updateSeo(Request $request, $blogId)
    {
        $seoData = [
            'blog_id' => $blogId,
            'route' => 'Blog Details',
            'name_route' => 'blog.details',
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'meta_robot' => $request->meta_robot,
            'canonical_url' => $request->canonical_url,
            'custom_url' => $request->custom_url,
            'json_ld' => $request->json_ld,
            'og_title' => $request->og_title,
            'og_description' => $request->og_description,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Handle meta keywords
        $metaKeywordsArray = json_decode($request->meta_keywords, true);
        if (is_array($metaKeywordsArray)) {
            $seoData['meta_keywords'] = implode(', ', array_column($metaKeywordsArray, 'value'));
        }

        // Handle og:image
        if ($request->hasFile('og_image')) {
            $filename = $blogId.'-'.$request->og_image->getClientOriginalName();
            $path = 'uploads/seo-og-images/'.$filename;

            app(FileUploaderService::class)->upload($request->og_image, $path, 600);
            $seoData['og_image'] = $path;

            $existing = SeoField::where('name_route', 'blog.details')->where('blog_id', $blogId)->first();
            if ($existing) {
                remove_file($existing->og_image);
            }
        }

        SeoField::updateOrCreate(
            ['name_route' => 'blog.details', 'blog_id' => $blogId],
            $seoData
        );
    }
}
