<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bootcamp;
use App\Models\BootcampCategory;
use App\Models\BootcampModule;
use App\Models\BootcampPurchase;
use App\Models\SeoField;
use App\Services\FileUploaderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BootcampController extends Controller
{
    public function index()
    {
        $query = Bootcamp::with('category')
            ->where('user_id', auth('web')->id());

        if ($search = request()->query('search')) {
            $query->where('title', 'like', "%$search%");
        }

        if ($categorySlug = request()->query('category')) {
            if ($categorySlug !== 'all') {
                $category = BootcampCategory::where('slug', $categorySlug)->first();
                if ($category) {
                    $query->where('category_id', $category->id);
                }
            }
        }

        if ($status = request()->query('status')) {
            if ($status !== 'all') {
                $query->where('status', $status === 'active' ? 1 : 0);
            }
        }

        if ($instructor = request()->query('instructor')) {
            if ($instructor !== 'all') {
                $query->where('user_id', $instructor);
            }
        }

        if ($priceFilter = request()->query('price')) {
            if ($priceFilter !== 'all') {
                switch ($priceFilter) {
                    case 'free':
                        $query->where('is_paid', 0);
                        break;
                    case 'paid':
                        $query->where('is_paid', 1);
                        break;
                    case 'discounted':
                        $query->where('discount_flag', 1);
                        break;
                }
            }
        }

        $page_data['bootcamps'] = $query->paginate(20)->appends(request()->query());

        return view('admin.bootcamp.index', $page_data);
    }

    public function create()
    {
        return view('admin.bootcamp.create');
    }

    public function edit($id)
    {
        $bootcamp = Bootcamp::where('id', $id)->where('user_id', auth('web')->id())->first();

        if (! $bootcamp) {
            Session::flash('error', get_phrase('Data not found.'));

            return redirect()->route('admin.bootcamps');
        }

        $page_data['bootcamp_details'] = $bootcamp;
        $page_data['modules'] = BootcampModule::where('bootcamp_id', $id)
            ->orderBy('sort', 'asc')
            ->get();

        return view('admin.bootcamp.edit', $page_data);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'required|string',
            'category_id' => 'required|exists:bootcamp_categories,id',
            'is_paid' => ['required', Rule::in(['0', '1'])],
            'price' => 'required_if:is_paid,1|nullable|numeric|min:1',
            'discount_flag' => ['nullable', Rule::in(['', '1'])],
            'discounted_price' => 'required_if:discount_flag,1|nullable|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $existingTitle = Bootcamp::where('user_id', auth('web')->id())
            ->where('title', $request->title)
            ->first();

        if ($existingTitle) {
            return back()->with('error', get_phrase('This title has already been used.'));
        }

        $data = [
            'user_id' => auth('web')->id(),
            'title' => $request->title,
            'slug' => slugify($request->title),
            'short_description' => $request->short_description,
            'description' => $request->description,
            'publish_date' => strtotime($request->publish_date),
            'category_id' => $request->category_id,
            'is_paid' => $request->is_paid,
            'price' => $request->price,
            'discount_flag' => $request->discount_flag,
            'discounted_price' => $request->discounted_price,
            'status' => 1,
        ];

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = 'uploads/bootcamp/thumbnail/'.nice_file_name($request->title, $request->thumbnail->extension());
            app(FileUploaderService::class)->upload($request->thumbnail, $data['thumbnail']);
        }

        $bootcampId = Bootcamp::insertGetId($data);

        return redirect()->route('admin.bootcamp.edit', [$bootcampId, 'tab' => 'basic'])
            ->with('success', get_phrase('Bootcamp has been created.'));
    }

    public function update(Request $request, $id)
    {
        $bootcamp = Bootcamp::where('id', $id)->where('user_id', auth('web')->id());

        if ($bootcamp->doesntExist() || ! $request->tab) {
            return back()->with('error', get_phrase('Data not found.'));
        }

        $data = [];
        $rules = [];

        switch ($request->tab) {
            case 'basic':
                $rules = [
                    'title' => 'required|string',
                    'description' => 'required|string',
                    'category_id' => 'required|exists:bootcamp_categories,id',
                ];

                $data['title'] = $request->title;
                $data['slug'] = slugify($request->title);
                $data['short_description'] = $request->short_description;
                $data['description'] = $request->description;
                $data['publish_date'] = strtotime($request->publish_date);
                $data['category_id'] = $request->category_id;

                // Check for duplicate title
                $titleExists = Bootcamp::where('user_id', auth('web')->id())
                    ->where('id', '!=', $id)
                    ->where('title', $request->title)
                    ->exists();

                if ($titleExists) {
                    return back()->with('error', get_phrase('This title has already been used.'));
                }
                break;

            case 'pricing':
                $rules = [
                    'is_paid' => Rule::in(['0', '1']),
                    'price' => 'required_if:is_paid,1|nullable|numeric|min:1',
                    'discount_flag' => Rule::in(['', '1']),
                    'discounted_price' => 'required_if:discount_flag,1|nullable|numeric|min:1',
                ];

                $data['is_paid'] = $request->is_paid;
                $data['price'] = $request->price;
                $data['discount_flag'] = $request->discount_flag;
                $data['discounted_price'] = $request->discounted_price;
                break;

            case 'info':
                $rules = [
                    'requirements' => 'nullable|array',
                    'outcomes' => 'nullable|array',
                    'faq_title' => 'nullable|array',
                    'faq_description' => 'nullable|array',
                ];

                $data['requirements'] = json_encode(array_filter($request->requirements ?? []));
                $data['outcomes'] = json_encode(array_filter($request->outcomes ?? []));

                $faqs = [];
                foreach ($request->faq_title ?? [] as $index => $title) {
                    if (! empty($title)) {
                        $faqs[] = [
                            'title' => $title,
                            'description' => $request->faq_description[$index] ?? '',
                        ];
                    }
                }
                $data['faqs'] = json_encode($faqs);
                break;

            case 'media':
                if ($request->hasFile('thumbnail')) {
                    $thumbnailPath = 'uploads/bootcamp/thumbnail/'.nice_file_name($request->title, $request->thumbnail->extension());
                    app(FileUploaderService::class)->upload($request->thumbnail, $thumbnailPath);

                    $existing = $bootcamp->first();
                    if ($existing && $existing->thumbnail) {
                        remove_file($existing->thumbnail);
                    }

                    $data['thumbnail'] = $thumbnailPath;
                }
                break;

            case 'seo':
                return $this->updateSeoFields($bootcamp->first(), $request);
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $bootcamp->update($data);

        return back()->with('success', get_phrase('Bootcamp has been updated successfully.'));
    }

    protected function updateSeoFields($bootcamp, $request)
    {
        $seo = SeoField::firstOrNew([
            'bootcamp_id' => $bootcamp->id,
        ]);

        $seo->fill([
            'route' => 'Bootcamp Details',
            'name_route' => 'bootcamp.details',
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'meta_robot' => $request->meta_robot,
            'canonical_url' => $request->canonical_url,
            'custom_url' => $request->custom_url,
            'json_ld' => $request->json_ld,
            'og_title' => $request->og_title,
            'og_description' => $request->og_description,
        ]);

        $keywords = json_decode($request->meta_keywords, true);
        if (is_array($keywords)) {
            $seo->meta_keywords = implode(', ', array_column($keywords, 'value'));
        }

        if ($request->hasFile('og_image')) {
            if ($seo->og_image) {
                remove_file($seo->og_image);
            }

            $ogImagePath = 'uploads/seo-og-images/'.$bootcamp->id.'-'.$request->og_image->getClientOriginalName();
            app(FileUploaderService::class)->upload($request->og_image, $ogImagePath, 600);
            $seo->og_image = $ogImagePath;
        }

        $seo->save();

        return back()->with('success', get_phrase('SEO settings updated.'));
    }

    public function delete($id)
    {
        $bootcamp = Bootcamp::where('id', $id)->where('user_id', auth('web')->id());

        if ($bootcamp->doesntExist()) {
            return back()->with('error', get_phrase('Data not found.'));
        }

        $bootcamp->delete();

        return back()->with('success', get_phrase('Bootcamp has been deleted.'));
    }

    public function duplicate($id)
    {
        $original = Bootcamp::find($id);

        if (! $original) {
            return back()->with('error', get_phrase('Data not found.'));
        }

        $copy = $original->replicate();
        $copy->title = $original->title.' copy';
        $copy->slug = slugify($copy->title);
        $copy->user_id = auth('web')->id();
        $copy->status = 1;
        $copy->save();

        return redirect()->route('admin.bootcamp.edit', [$copy->id, 'tab' => 'basic'])
            ->with('success', get_phrase('Bootcamp has been duplicated.'));
    }

    public function status($id)
    {
        $bootcamp = Bootcamp::where('id', $id)->where('user_id', auth('web')->id());

        if ($bootcamp->doesntExist()) {
            return response()->json(['error' => get_phrase('Data not found.')], 404);
        }

        $bootcamp->update(['status' => ! $bootcamp->first()->status]);

        return back()->with('success', get_phrase('Status has been updated.'));
    }

    public function purchase_history()
    {
        $page_data['purchases'] = BootcampPurchase::join('bootcamps', 'bootcamp_purchases.bootcamp_id', '=', 'bootcamps.id')
            ->select(
                'bootcamp_purchases.*',
                'bootcamps.user_id as author',
                'bootcamps.title',
                'bootcamps.slug',
                'bootcamps.price as amount'
            )
            ->latest('bootcamp_purchases.id')
            ->paginate(20)
            ->appends(request()->query());

        return view('admin.bootcamp.purchase_history', $page_data);
    }

    public function invoice($id)
    {
        $invoice = BootcampPurchase::join('bootcamps', 'bootcamp_purchases.bootcamp_id', '=', 'bootcamps.id')
            ->where('bootcamp_purchases.id', $id)
            ->select(
                'bootcamp_purchases.*',
                'bootcamps.title',
                'bootcamps.slug'
            )
            ->first();

        if (! $invoice) {
            return back()->with('error', get_phrase('Data not found.'));
        }

        $page_data['invoice'] = $invoice;

        return view('admin.bootcamp.invoice', $page_data);
    }
}
