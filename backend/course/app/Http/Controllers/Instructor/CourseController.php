<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use App\Models\Section;
use App\Models\SeoField;
use App\Services\FileUploaderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth('web')->id();
        $query = Course::where('user_id', $userId);

        $page_data = [
            'status' => $request->get('status', 'all'),
            'instructor' => $request->get('instructor', 'all'),
            'price' => $request->get('price', 'all'),
        ];

        if ($request->filled('category') && $request->category !== 'all') {
            $category = Category::where('slug', $request->category)->first();

            if ($category) {
                if ($category->parent_id === 0) {
                    $subCategoryIds = Category::where('parent_id', $category->id)->pluck('id');
                    $query->whereIn('category_id', $subCategoryIds);
                    $page_data['parent_cat'] = $request->category;
                } else {
                    $query->where('category_id', $category->id);
                    $page_data['child_cat'] = $request->category;
                }
            }
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', "%{$request->search}%");
        }

        if ($request->filled('price') && $request->price !== 'all') {
            $query->where('is_paid', $request->price === 'free' ? 0 : 1);
        }

        if ($request->filled('instructor') && $request->instructor !== 'all') {
            $query->where('user_id', $request->instructor);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $page_data['courses'] = $query->orderByDesc('id')->paginate(20)->appends($request->query());

        // Course stats
        $page_data['pending_courses'] = Course::where('user_id', $userId)->where('status', 'pending')->count();
        $page_data['active_courses'] = Course::where('user_id', $userId)->where('status', 'active')->count();
        $page_data['upcoming_courses'] = Course::where('user_id', $userId)->where('status', 'upcoming')->count();
        $page_data['paid_courses'] = Course::where('user_id', $userId)->where('is_paid', 1)->count();
        $page_data['free_courses'] = Course::where('user_id', $userId)->where('is_paid', 0)->count();

        return view('instructor.course.index', $page_data);
    }

    public function create()
    {
        return view('instructor.course.create');
    }

    public function store(Request $request)
    {
        $rules = [
            'title' => 'required|max:255',
            'category_id' => 'required|numeric|min:1',
            'course_type' => 'required|in:general,scorm',
            'level' => 'required|in:everyone,beginner,intermediate,advanced',
            'language' => 'required',
            'is_paid' => Rule::in(['0', '1']),
            'price' => 'required_if:is_paid,1|min:1|nullable|numeric',
            'discount_flag' => Rule::in(['', '1']),
            'discounted_price' => 'required_if:discount_flag,1|min:1|nullable|numeric',
            'requirements' => 'array',
            'outcomes' => 'array',
            'faqs' => 'array',
            'instructors' => 'required|array|min:1',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['validationError' => $validator->errors()]);
        }

        $data = [
            'title' => $request->title,
            'slug' => slugify($request->title),
            'user_id' => auth('web')->id(),
            'category_id' => $request->category_id,
            'course_type' => $request->course_type,
            'status' => 'pending',
            'level' => $request->level,
            'language' => strtolower($request->language),
            'is_paid' => $request->is_paid,
            'price' => $request->price,
            'discount_flag' => $request->discount_flag,
            'discounted_price' => $request->discounted_price,
            'enable_drip_content' => $request->enable_drip_content,
            'drip_content_settings' => '{"lesson_completion_role":"percentage","minimum_duration":15,"minimum_percentage":"30","locked_lesson_message":"<h3><strong>Permission denied!</strong></h3><p>This course supports drip content, so you must complete the previous lessons.</p>"}',
            'meta_keywords' => collect(json_decode($request->meta_keywords, true))
                ->pluck('value')
                ->implode(','),
            'meta_description' => $request->meta_description,
            'short_description' => $request->short_description,
            'description' => $request->description,
            'expiry_period' => $request->expiry_period === 'limited_time' && is_numeric($request->number_of_month)
                ? $request->number_of_month
                : null,
            'requirements' => json_encode(array_filter($request->requirements ?? [])),
            'outcomes' => json_encode(array_filter($request->outcomes ?? [])),
            'faqs' => json_encode(array_map(
                fn ($title, $desc) => ['title' => $title, 'description' => $desc],
                $request->faq_title ?? [],
                $request->faq_description ?? []
            )),
            'instructor_ids' => json_encode($request->instructors),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // File uploads
        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = 'uploads/course-thumbnail/'.nice_file_name($request->title, $request->thumbnail->extension());
            app(FileUploaderService::class)->upload($request->thumbnail, $data['thumbnail'], 400, null, 200, 200);
        }

        if ($request->hasFile('banner')) {
            $data['banner'] = 'uploads/course-banner/'.nice_file_name($request->title, $request->banner->extension());
            app(FileUploaderService::class)->upload($request->banner, $data['banner'], 400, null, 200, 200);
        }

        if ($request->hasFile('preview')) {
            $data['preview'] = 'uploads/course-preview/'.nice_file_name($request->title, $request->preview->extension());
            app(FileUploaderService::class)->upload($request->preview, $data['preview']);
        }

        $courseId = Course::insertGetId($data);
        Course::where('id', $courseId)->update(['slug' => slugify("{$request->title}-{$courseId}")]);

        Session::flash('success', get_phrase('Course added successfully'));

        return ['redirectTo' => route('instructor.course.edit', ['id' => $courseId])];
    }

    public function edit(Request $request, $course_id = '')
    {
        $data['course_details'] = Course::findOrFail($course_id);
        $data['sections'] = Section::where('course_id', $course_id)->orderBy('sort')->get();

        return view('instructor.course.edit', $data);
    }

    public function update(Request $request, $id)
    {
        $course = Course::where('id', $id)->firstOrFail();

        if (empty($request->tab)) {
            Session::flash('error', get_phrase('Data not found.'));

            return redirect()->back();
        }

        $rules = $data = [];

        switch ($request->tab) {
            case 'basic':
                $rules = [
                    'title' => 'required|max:255',
                    'category_id' => 'required|numeric|min:1',
                    'level' => 'required|in:everyone,beginner,intermediate,advanced',
                    'language' => 'required',
                    'status' => 'required|in:active,pending,draft,private,upcoming,inactive',
                    'instructors' => 'required|array|min:1',
                ];

                $data = [
                    'title' => $request->title,
                    'slug' => slugify($request->title.'-'.$id),
                    'short_description' => $request->short_description,
                    'description' => $request->description,
                    'category_id' => $request->category_id,
                    'level' => $request->level,
                    'language' => strtolower($request->language),
                    'status' => $request->status,
                    'instructor_ids' => json_encode($request->instructors),
                ];
                break;

            case 'pricing':
                $rules = [
                    'is_paid' => Rule::in(['0', '1']),
                    'price' => 'required_if:is_paid,1|min:1|nullable|numeric',
                    'discount_flag' => Rule::in(['', '1']),
                    'discounted_price' => 'required_if:discount_flag,1|min:1|nullable|numeric',
                ];

                $data = [
                    'is_paid' => $request->is_paid,
                    'price' => $request->price,
                    'discount_flag' => $request->discount_flag,
                    'discounted_price' => $request->discounted_price,
                    'expiry_period' => ($request->expiry_period === 'limited_time' && is_numeric($request->number_of_month))
                        ? $request->number_of_month
                        : null,
                ];
                break;

            case 'info':
                $rules = [
                    'requirements' => 'array',
                    'outcomes' => 'array',
                    'faqs' => 'array',
                ];

                $faqs = [];
                foreach ($request->faq_title ?? [] as $index => $title) {
                    if (! empty($title)) {
                        $faqs[] = [
                            'title' => $title,
                            'description' => $request->faq_description[$index] ?? '',
                        ];
                    }
                }

                $data = [
                    'requirements' => json_encode(array_filter($request->requirements ?? [])),
                    'outcomes' => json_encode(array_filter($request->outcomes ?? [])),
                    'faqs' => json_encode($faqs),
                ];
                break;

            case 'media':
                if ($request->hasFile('thumbnail')) {
                    $data['thumbnail'] = 'uploads/course-thumbnail/'.nice_file_name($request->title, $request->thumbnail->extension());
                    app(FileUploaderService::class)->upload($request->thumbnail, $data['thumbnail'], 400, null, 200, 200);
                    remove_file($course->thumbnail);
                }

                if ($request->hasFile('banner')) {
                    $data['banner'] = 'uploads/course-banner/'.nice_file_name($request->title, $request->banner->extension());
                    app(FileUploaderService::class)->upload($request->banner, $data['banner'], 1400, null, 300, 300);
                    remove_file($course->banner);
                }

                if ($request->preview_video_provider === 'link') {
                    $data['preview'] = $request->preview_link;
                } elseif ($request->preview_video_provider === 'file' && $request->hasFile('preview')) {
                    $data['preview'] = 'uploads/course-preview/'.nice_file_name($request->title, $request->preview->extension());
                    app(FileUploaderService::class)->upload($request->preview, $data['preview']);
                    remove_file($course->preview);
                }
                break;

            case 'seo':
                $seo_data = [
                    'course_id' => $id,
                    'route' => 'Course Details',
                    'name_route' => 'course.details',
                    'meta_title' => $request->meta_title,
                    'meta_description' => $request->meta_description,
                    'meta_robot' => $request->meta_robot,
                    'canonical_url' => $request->canonical_url,
                    'custom_url' => $request->custom_url,
                    'json_ld' => $request->json_ld,
                    'og_title' => $request->og_title,
                    'og_description' => $request->og_description,
                    'meta_keywords' => collect(json_decode($request->meta_keywords, true))
                        ->pluck('value')->implode(','),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if ($request->hasFile('og_image')) {
                    $filename = $course->id.'-'.$request->og_image->getClientOriginalName();
                    $path = 'uploads/seo-og-images/'.$filename;
                    app(FileUploaderService::class)->upload($request->og_image, $path, 600);
                    $seo_data['og_image'] = $path;
                }

                $seo = SeoField::where('name_route', 'course.details')->where('course_id', $id)->first();
                if ($seo) {
                    if (isset($seo_data['og_image'])) {
                        remove_file($seo->og_image);
                    }
                    $seo->update($seo_data);
                } else {
                    SeoField::create($seo_data);
                }
                break;

            case 'drip-content':
                $rules = ['enable_drip_content' => Rule::in(['0', '1'])];

                $timeParts = explode(':', $request->minimum_duration);
                $seconds = ($timeParts[0] * 3600) + ($timeParts[1] * 60) + ($timeParts[2] ?? 0);

                $data = [
                    'enable_drip_content' => $request->enable_drip_content,
                    'drip_content_settings' => json_encode([
                        'lesson_completion_role' => htmlspecialchars($request->lesson_completion_role),
                        'minimum_duration' => $seconds,
                        'minimum_percentage' => htmlspecialchars($request->minimum_percentage),
                        'locked_lesson_message' => htmlspecialchars($request->locked_lesson_message),
                    ]),
                ];
                break;
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['validationError' => $validator->errors()]);
        }

        $course->update($data);

        return ['success' => get_phrase('Course updated successfully')];
    }

    public function delete($id)
    {
        $course = Course::findOrFail($id);

        remove_file($course->thumbnail);
        remove_file($course->banner);
        remove_file($course->preview);

        $course->delete();

        return redirect()
            ->route('instructor.courses')
            ->with('success', get_phrase('Course deleted successfully'));
    }

    public function status($type, $id)
    {
        $validStatuses = ['active', 'pending', 'inactive', 'upcoming', 'private', 'draft'];
        $status = in_array($type, $validStatuses) ? $type : 'draft';

        Course::where('id', $id)->update(['status' => $status]);

        return redirect()
            ->route('admin.courses')
            ->with('success', get_phrase('Course status changed successfully'));
    }

    public function draft($id)
    {
        $course = Course::find($id);

        if (! $course) {
            return response()->json([
                'error' => get_phrase('Data not found.'),
            ]);
        }

        $newStatus = $course->status === 'active' ? 'inactive' : 'active';
        $course->update(['status' => $newStatus]);

        return response()->json([
            'success' => get_phrase('Status has been updated.'),
        ]);
    }

    public function duplicate($id)
    {
        $query = Course::where('id', $id);

        if (auth('web')->user()->role !== 'admin') {
            $query->where('user_id', auth('web')->id());
        }

        $original = $query->first();

        if (! $original) {
            Session::flash('error', get_phrase('Data not found.'));

            return redirect()->back();
        }

        $data = $original->replicate()->toArray();
        unset($data['id'], $data['created_at'], $data['updated_at']);
        $data['status'] = 'pending';

        if (auth('web')->user()->role === 'admin') {
            $data['user_id'] = auth('web')->id();
        }

        $newCourseId = Course::insertGetId($data);
        Course::where('id', $newCourseId)->update([
            'slug' => slugify($original->title).'-'.$newCourseId,
        ]);

        Session::flash('success', get_phrase('Course duplicated.'));

        return redirect()->route('instructor.course.edit', $newCourseId);
    }
}
