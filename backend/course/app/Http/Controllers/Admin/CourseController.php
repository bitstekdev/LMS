<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\Mailer;
use App\Models\Category;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\QuizSubmission;
use App\Models\Section;
use App\Models\SeoField;
use App\Services\FileUploaderService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $category = 'all';
        $status = 'all';
        $instructor = 'all';
        $price = 'all';

        $query = Course::query();

        if (! empty($request->category) && $request->category !== 'all') {
            $category_details = Category::where('slug', $request->category)->first();
            if ($category_details && $category_details->parent_id > 0) {
                $page_data['child_cat'] = $request->category;
                $query->where('category_id', $category_details->id);
            } elseif ($category_details) {
                $sub_cat_id = Category::where('parent_id', $category_details->id)->pluck('id')->toArray();
                $sub_cat_id[] = $category_details->id;
                $query->whereIn('category_id', $sub_cat_id);
                $page_data['parent_cat'] = $request->category;
            }
        }

        // search filter
        if (! empty($request->search)) {
            $query->where('title', 'LIKE', '%'.$request->search.'%');
        }

        // selected price
        if (! empty($request->price) && $request->price !== 'all') {
            $search_price = $request->price === 'free' ? 0 : ($request->price === 'paid' ? 1 : 2);
            $query->where('is_paid', $search_price);
            $price = $request->price;
        }

        // selected instructor
        if (! empty($request->instructor) && $request->instructor !== 'all') {
            $query->where('user_id', $request->instructor);
            $instructor = $request->instructor;
        }

        // status filter
        if (! empty($request->status) && $request->status !== 'all') {
            $allowed = ['active', 'inactive', 'pending', 'private', 'upcoming', 'draft'];
            if (in_array($request->status, $allowed, true)) {
                $query->where('status', $request->status);
            }
            $status = $request->status;
        }

        $page_data['status'] = $status;
        $page_data['instructor'] = $instructor;
        $page_data['price'] = $price;
        $page_data['courses'] = $query->orderByDesc('id')->paginate(20)->appends($request->query());
        $page_data['pending_courses'] = Course::where('status', 'pending')->count();
        $page_data['active_courses'] = Course::where('status', 'active')->count();
        $page_data['upcoming_courses'] = Course::where('status', 'upcoming')->count();
        $page_data['paid_courses'] = Course::where('is_paid', 1)->count();
        $page_data['free_courses'] = Course::where('is_paid', 0)->count();

        return view('admin.course.index', $page_data);
    }

    public function create()
    {
        return view('admin.course.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|max:255',
            'category_id' => 'required|numeric|min:1',
            'course_type' => 'required|in:general,scorm',
            'status' => 'required|in:active,pending,draft,private,upcoming,inactive',
            'level' => 'required|in:everyone,beginner,intermediate,advanced',
            'language' => 'required',
            'is_paid' => Rule::in(['0', '1']),
            'price' => 'required_if:is_paid,1|min:1|nullable|numeric',
            'discount_flag' => Rule::in(['', '1']),
            'discounted_price' => 'required_if:discount_flag,1|min:1|nullable|numeric',
            'enable_drip_content' => Rule::in(['0', '1']),
            'requirements' => 'array',
            'outcomes' => 'array',
            'faqs' => 'array',
            'instructors' => 'required|array|min:1',
        ]);

        $data = [
            'title' => $request->title,
            'slug' => slugify($request->title),
            'user_id' => auth('web')->id(),
            'category_id' => $request->category_id,
            'course_type' => $request->course_type,
            'status' => $request->status,
            'level' => $request->level,
            'language' => strtolower($request->language),
            'is_paid' => $request->is_paid,
            'price' => $request->price,
            'discount_flag' => $request->discount_flag,
            'discounted_price' => $request->discounted_price,
            'enable_drip_content' => $request->enable_drip_content,
            'short_description' => $request->short_description,
            'description' => $request->description,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // default drip content settings
        $data['drip_content_settings'] = '{"lesson_completion_role":"percentage","minimum_duration":15,"minimum_percentage":"30","locked_lesson_message":"&lt;h3 xss=&quot;removed&quot; style=&quot;text-align: center; &quot;&gt;&lt;span xss=&quot;removed&quot;&gt;&lt;strong&gt;Permission denied!&lt;\/strong&gt;&lt;\/span&gt;&lt;\/h3&gt;&lt;p xss=&quot;removed&quot; style=&quot;text-align: center; &quot;&gt;&lt;span xss=&quot;removed&quot;&gt;This course supports drip content, so you must complete the previous lessons.&lt;\/span&gt;&lt;\/p&gt;"}';

        // meta keywords
        $meta_keywords = '';
        $meta_keywords_arr = json_decode($request->meta_keywords, true);
        if (is_array($meta_keywords_arr)) {
            foreach ($meta_keywords_arr as $k => $item) {
                $meta_keywords .= ($k ? ',' : '').$item['value'];
            }
        }
        $data['meta_keywords'] = $meta_keywords;
        $data['meta_description'] = $request->meta_description;

        // Course expiry period
        $data['expiry_period'] = ($request->expiry_period === 'limited_time' && is_numeric($request->number_of_month) && (int) $request->number_of_month > 0)
            ? (int) $request->number_of_month
            : null;

        // arrays
        if (! empty($request->requirements)) {
            $data['requirements'] = json_encode(array_filter($request->requirements, fn ($v) => $v !== null && $v !== ''));
        }
        if (! empty($request->outcomes)) {
            $data['outcomes'] = json_encode(array_filter($request->outcomes, fn ($v) => $v !== null && $v !== ''));
        }
        if (! empty($request->faq_title)) {
            $faqs = [];
            foreach ($request->faq_title as $key => $title) {
                if ($title !== '') {
                    $faqs[] = ['title' => $title, 'description' => $request->faq_description[$key] ?? ''];
                }
            }
            $data['faqs'] = json_encode($faqs);
        }

        $data['instructor_ids'] = json_encode($request->instructors);

        // MEDIA (use FileUploaderService)
        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = 'uploads/course-thumbnail/'.nice_file_name($request->title, $request->file('thumbnail')->extension());
            app(FileUploaderService::class)->upload($request->file('thumbnail'), $data['thumbnail'], 400, null, 200, 200);
        }

        if ($request->hasFile('banner')) {
            $data['banner'] = 'uploads/course-banner/'.nice_file_name($request->title, $request->file('banner')->extension());
            app(FileUploaderService::class)->upload($request->file('banner'), $data['banner'], 1400, null, 300, 300);
        }

        if ($request->hasFile('preview')) {
            $data['preview'] = 'uploads/course-preview/'.nice_file_name($request->title, $request->file('preview')->extension());
            app(FileUploaderService::class)->upload($request->file('preview'), $data['preview']);
        }

        $course_id = Course::insertGetId($data);
        Course::where('id', $course_id)->update(['slug' => slugify($request->title.'-'.$course_id)]);

        return redirect()->route('admin.course.edit', ['id' => $course_id])->with('success', get_phrase('Course added successfully'));
    }

    public function edit($course_id, Request $request)
    {
        $data['course_details'] = Course::where('id', $course_id)->first();
        $data['sections'] = Section::where('course_id', $course_id)->orderBy('sort')->get();

        return view('admin.course.edit', $data);
    }

    public function update(Request $request, $id)
    {
        $query = Course::where('id', $id);

        if (empty($request->tab)) {
            Session::flash('error', get_phrase('Data not found.'));

            return redirect()->back();
        }

        $rules = [];
        $data = [];

        if ($request->tab === 'basic') {
            $rules = [
                'title' => 'required|max:255',
                'category_id' => 'required|numeric|min:1',
                'level' => 'required|in:everyone,beginner,intermediate,advanced',
                'language' => 'required',
                'status' => 'required|in:active,pending,draft,private,upcoming,inactive',
                'instructors' => 'required|array|min:1',
            ];

            $data['title'] = $request->title;
            $data['slug'] = slugify($request->title.'-'.$id);
            $data['short_description'] = $request->short_description;
            $data['description'] = removeScripts($request->description);
            $data['category_id'] = $request->category_id;
            $data['level'] = $request->level;
            $data['language'] = strtolower($request->language);
            $data['status'] = $request->status;
            $data['instructor_ids'] = json_encode($request->instructors);

        } elseif ($request->tab === 'pricing') {
            $rules = [
                'is_paid' => Rule::in(['0', '1']),
                'price' => 'required_if:is_paid,1|min:1|nullable|numeric',
                'discount_flag' => Rule::in(['', '1']),
                'discounted_price' => 'required_if:discount_flag,1|min:1|nullable|numeric',
            ];

            $data['is_paid'] = $request->is_paid;
            $data['price'] = $request->price;
            $data['discount_flag'] = $request->discount_flag;
            $data['discounted_price'] = $request->discounted_price;

            $data['expiry_period'] = ($request->expiry_period === 'limited_time' && is_numeric($request->number_of_month) && (int) $request->number_of_month > 0)
                ? (int) $request->number_of_month
                : null;

        } elseif ($request->tab === 'info') {
            $rules = [
                'requirements' => 'array',
                'outcomes' => 'array',
                'faqs' => 'array',
            ];

            $data['requirements'] = json_encode(array_filter($request->requirements ?? [], fn ($v) => $v !== null && $v !== ''));
            $data['outcomes'] = json_encode(array_filter($request->outcomes ?? [], fn ($v) => $v !== null && $v !== ''));

            $faqs = [];
            foreach (($request->faq_title ?? []) as $key => $title) {
                if ($title !== '') {
                    $faqs[] = ['title' => $title, 'description' => $request->faq_description[$key] ?? ''];
                }
            }
            $data['faqs'] = json_encode($faqs);

        } elseif ($request->tab === 'media') {
            // thumbnail
            if ($request->hasFile('thumbnail')) {
                $data['thumbnail'] = 'uploads/course-thumbnail/'.nice_file_name($request->title, $request->file('thumbnail')->extension());
                app(FileUploaderService::class)->upload($request->file('thumbnail'), $data['thumbnail'], 400, null, 200, 200);
                remove_file(optional($query->first())->thumbnail);
            }

            // banner
            if ($request->hasFile('banner')) {
                $data['banner'] = 'uploads/course-banner/'.nice_file_name($request->title, $request->file('banner')->extension());
                app(FileUploaderService::class)->upload($request->file('banner'), $data['banner'], 1400, null, 300, 300);
                remove_file(optional($query->first())->banner);
            }

            // preview
            if ($request->preview_video_provider === 'link') {
                $data['preview'] = $request->preview_link;
            } elseif ($request->preview_video_provider === 'file' && $request->hasFile('preview')) {
                $data['preview'] = 'uploads/course-preview/'.nice_file_name($request->title, $request->file('preview')->extension());
                app(FileUploaderService::class)->upload($request->file('preview'), $data['preview']);
                remove_file(optional($query->first())->preview);
            }

        } elseif ($request->tab === 'seo') {
            $course_details = $query->first();
            $SeoField = SeoField::where('name_route', 'course.details')->where('course_id', $course_details->id)->first();

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
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $meta_keywords_arr = json_decode($request->meta_keywords, true);
            if (is_array($meta_keywords_arr)) {
                $seo_data['meta_keywords'] = implode(', ', array_map(fn ($i) => $i['value'], $meta_keywords_arr));
            }

            if ($request->hasFile('og_image')) {
                $originalFileName = $course_details->id.'-'.$request->file('og_image')->getClientOriginalName();
                $destinationPath = 'uploads/seo-og-images/'.$originalFileName;
                app(FileUploaderService::class)->upload($request->file('og_image'), $destinationPath, 600);
                $seo_data['og_image'] = $destinationPath;
            }

            if ($SeoField) {
                if (! empty($seo_data['og_image'])) {
                    remove_file($SeoField->og_image);
                }
                SeoField::where('name_route', 'course.details')->where('course_id', $course_details->id)->update($seo_data);
            } else {
                SeoField::insert($seo_data);
            }

        } elseif ($request->tab === 'drip-content') {
            $rules = [
                'enable_drip_content' => Rule::in(['0', '1']),
            ];

            $data['enable_drip_content'] = $request->enable_drip_content;

            $lesson_completion_role = htmlspecialchars($request->input('lesson_completion_role'));
            $minimum_duration_input = htmlspecialchars($request->input('minimum_duration'));
            $minimum_percentage = htmlspecialchars($request->input('minimum_percentage'));
            $locked_lesson_message = htmlspecialchars($request->input('locked_lesson_message'));

            // Convert HH:MM:SS to seconds safely
            $seconds = 0;
            if ($minimum_duration_input) {
                $time_parts = array_map('intval', explode(':', $minimum_duration_input));
                $time_parts = array_pad($time_parts, 3, 0);
                $seconds = ($time_parts[0] * 3600) + ($time_parts[1] * 60) + $time_parts[2];
            }

            $drip_data = [
                'lesson_completion_role' => $lesson_completion_role,
                'minimum_duration' => $seconds,
                'minimum_percentage' => $minimum_percentage,
                'locked_lesson_message' => $locked_lesson_message,
            ];
            $data['drip_content_settings'] = json_encode($drip_data);
        }

        // Validate per-tab payload (AJAX-friendly)
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return ['validationError' => $validator->getMessageBag()->toArray()];
        }

        $query->update($data);

        return ['success' => get_phrase('Course updated successfully')];
    }

    public function status($type, $id)
    {
        $map = [
            'active' => 'active',
            'pending' => 'pending',
            'inactive' => 'inactive',
            'upcoming' => 'upcoming',
            'private' => 'private',
            'draft' => 'draft',
        ];
        Course::where('id', $id)->update(['status' => $map[$type] ?? 'draft']);

        return redirect()->route('admin.courses')->with('success', get_phrase('Course status changed successfully'));
    }

    public function delete($id)
    {
        $course = Course::find($id);
        if (! $course) {
            Session::flash('error', get_phrase('Course not found'));

            return redirect()->back();
        }

        $lessons = Lesson::where('course_id', $id)->get();
        foreach ($lessons as $lesson) {
            remove_file($lesson->lesson_src);
            remove_file('uploads/lesson_file/attachment/'.$lesson->attachment);

            if ($lesson->lesson_type === 'quiz') {
                Question::where('quiz_id', $lesson->id)->each(fn ($q) => $q->delete());
                QuizSubmission::where('quiz_id', $lesson->id)->each(fn ($s) => $s->delete());
            }
            $lesson->delete();
        }

        remove_file($course->thumbnail);
        remove_file($course->banner);
        remove_file($course->preview);

        $course->delete();

        return redirect()->route('admin.courses')->with('success', get_phrase('Course deleted successfully'));
    }

    public function draft($id)
    {
        $course = Course::find($id);
        if (! $course) {
            return json_encode(['error' => get_phrase('Data not found.')]);
        }

        $status = $course->status === 'active' ? 'deactivate' : 'active';
        Course::where('id', $id)->update(['status' => $status]);

        return json_encode(['success' => get_phrase('Status has been updated.')]);
    }

    public function duplicate($id)
    {
        $course = Course::query()->where('id', $id);
        if (auth('web')->user()->role !== 'admin') {
            $course->where('user_id', auth('web')->id());
        }

        if ($course->doesntExist()) {
            Session::flash('error', get_phrase('Data not found.'));

            return redirect()->back();
        }

        $data = $course->first()->toArray();
        if (auth('web')->user()->role === 'admin') {
            $data['user_id'] = auth('web')->id();
        }
        unset($data['id'], $data['created_at'], $data['updated_at']);

        $course_id = Course::insertGetId($data);
        Course::where('id', $course_id)->update(['slug' => slugify($data['title']).'-'.$course_id]);

        Session::flash('success', get_phrase('Course duplicated.'));

        return redirect()->route('admin.course.edit', $course_id);
    }

    public function approval(Request $request, $id)
    {
        Course::where('id', $id)->update(['status' => 'active']);
        $course = Course::where('id', $id)->first();

        // Configure mailer dynamically
        config([
            'mail.mailers.smtp.transport' => get_settings('protocol'),
            'mail.mailers.smtp.host' => get_settings('smtp_host'),
            'mail.mailers.smtp.port' => get_settings('smtp_port'),
            'mail.mailers.smtp.encryption' => get_settings('smtp_crypto'),
            'mail.mailers.smtp.username' => get_settings('smtp_from_email'),
            'mail.mailers.smtp.password' => get_settings('smtp_pass'),
            'mail.from.address' => get_settings('smtp_from_email'),
            'mail.from.name' => get_settings('smtp_user'),
        ]);

        $mail_data = [
            'subject' => $request->subject,
            'description' => $request->message,
        ];

        try {
            Mail::to($course->user->email)->send(new Mailer($mail_data));
        } catch (Exception $e) {
            // silently fail
        }

        Session::flash('success', get_phrase('Course activated successfully'));

        return redirect()->route('admin.courses');
    }
}
