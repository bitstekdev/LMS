<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\TeamPackagePurchase;
use App\Models\TeamTrainingPackage;
use App\Services\FileUploaderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TeamTrainingController extends Controller
{
    public function index()
    {
        $query = TeamTrainingPackage::with('course')
            ->where('user_id', auth('web')->id());

        if (request()->has('search')) {
            $query->where('title', 'like', '%'.request()->query('search').'%');
        }

        $page_data['packages'] = $query->paginate(20)->appends(request()->query());

        return view('instructor.team_training.index', $page_data);
    }

    public function store(Request $request)
    {
        $package = $request->only([
            'title', 'course_privacy', 'course_id', 'allocation', 'pricing_type', 'price', 'expiry_type', 'expiry_date', 'thumbnail',
        ]);
        $package['slug'] = slugify($package['title']);

        Validator::make($package, [
            'title' => ['required', Rule::unique('team_training_packages')->where(fn ($q) => $q->where('user_id', auth('web')->id()))],
            'slug' => ['required', Rule::unique('team_training_packages')->where(fn ($q) => $q->where('user_id', auth('web')->id()))],
            'course_privacy' => 'required|in:public,private',
            'allocation' => 'required|numeric|min:0',
            'pricing_type' => 'required|in:0,1',
            'price' => 'required_if:is_paid,1',
            'expiry_type' => 'required_if:is_paid,1|in:limited,lifetime',
            'expiry_date' => 'required_if:expiry_type,limited',
            'thumbnail' => 'required|file|mimes:jpg,jpeg,png',
        ])->validate();

        if ($package['expiry_type'] === 'limited') {
            [$start, $end] = explode('-', $package['expiry_date']);
            $package['start_date'] = strtotime(trim($start));
            $package['expiry_date'] = strtotime(trim($end));
        }

        unset($package['thumbnail']);
        if ($request->hasFile('thumbnail')) {
            $path = 'uploads/team_training/thumbnail/'.nice_file_name($package['title'], $request->thumbnail->extension());
            app(FileUploaderService::class)->upload($request->thumbnail, $path);
            $package['thumbnail'] = $path;
        }

        $package['user_id'] = auth('web')->id();
        $package['features'] = json_encode($request->features ?? []);
        $package['status'] = 1;

        TeamTrainingPackage::create($package);

        return back()->with('success', get_phrase('Package has been created.'));
    }

    public function edit($id)
    {
        $page_data['package'] = TeamTrainingPackage::with('course')->findOrFail($id);

        return view('instructor.team_training.edit', $page_data);
    }

    public function update(Request $request, $id)
    {
        $package = $request->only([
            'title', 'course_privacy', 'course_id', 'allocation', 'pricing_type', 'price', 'expiry_type', 'expiry_date', 'thumbnail',
        ]);
        $package['slug'] = slugify($package['title']);

        Validator::make($package, [
            'title' => ['required', Rule::unique('team_training_packages')->ignore($id)->where(fn ($q) => $q->where('user_id', auth('web')->id()))],
            'slug' => ['required', Rule::unique('team_training_packages')->ignore($id)->where(fn ($q) => $q->where('user_id', auth('web')->id()))],
            'course_privacy' => 'required|in:public,private',
            'allocation' => 'required|numeric|min:0',
            'pricing_type' => 'required|in:0,1',
            'price' => 'required_if:is_paid,1',
            'expiry_type' => 'required_if:is_paid,1|in:limited,lifetime',
            'expiry_date' => 'required_if:expiry_type,limited',
        ])->validate();

        if ($package['expiry_type'] === 'limited') {
            [$start, $end] = explode('-', $package['expiry_date']);
            $package['start_date'] = strtotime(trim($start));
            $package['expiry_date'] = strtotime(trim($end));
        }

        unset($package['thumbnail']);
        if ($request->hasFile('thumbnail')) {
            $path = 'uploads/team_training/thumbnail/'.nice_file_name($package['title'], $request->thumbnail->extension());
            app(FileUploaderService::class)->upload($request->thumbnail, $path);
            $package['thumbnail'] = $path;
        }

        $package['user_id'] = auth('web')->id();
        $package['features'] = json_encode(array_values(array_filter($request->features ?? [])));

        TeamTrainingPackage::findOrFail($id)->update($package);

        return back()->with('success', get_phrase('Package has been updated.'));
    }

    public function delete($id)
    {
        TeamTrainingPackage::findOrFail($id)->delete();

        return back()->with('error', get_phrase('Package has been deleted.'));
    }

    public function duplicate($id)
    {
        $original = TeamTrainingPackage::findOrFail($id);
        $copy = $original->replicate();
        $copy->title = $original->title.' copy';
        $copy->slug = slugify($copy->title);
        $copy->save();

        return to_route('instructor.team.packages.edit', $copy->id)
            ->with('success', get_phrase('Package has been copied.'));
    }

    public function get_courses(Request $request)
    {
        if (! in_array($request->privacy, ['public', 'private'])) {
            abort(400);
        }

        $status = $request->privacy === 'public' ? 'active' : 'private';
        $courses = Course::where('status', $status)->get();

        return view('instructor.team_training.load_courses', compact('courses'));
    }

    public function get_course_price(Request $request)
    {
        return Course::where('id', $request->course_id)->value('price') ?? 0;
    }

    public function toggle_status($id)
    {
        $package = TeamTrainingPackage::findOrFail($id);
        $package->status = ! $package->status;
        $package->save();

        return response()->json(['success' => get_phrase('Status has been updated.')]);
    }

    public function purchase_history()
    {
        $page_data['purchases'] = TeamPackagePurchase::with('package')
            ->whereHas('package', fn ($q) => $q->where('user_id', auth('web')->id()))
            ->latest('id')
            ->paginate(20)
            ->appends(request()->query());

        return view('instructor.team_training.purchase_history', $page_data);
    }

    public function invoice($id)
    {
        $page_data['invoice'] = TeamPackagePurchase::with('package')
            ->whereHas('package', fn ($q) => $q->where('user_id', auth('web')->id()))
            ->where('id', $id)
            ->firstOrFail();

        return view('instructor.team_training.invoice', $page_data);
    }
}
