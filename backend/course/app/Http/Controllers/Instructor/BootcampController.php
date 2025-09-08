<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Bootcamp;
use App\Models\BootcampCategory;
use App\Models\BootcampModule;
use App\Models\BootcampPurchase;
use App\Models\SeoField;
use App\Services\FileUploaderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BootcampController extends Controller
{
    public function index()
    {
        $query = Bootcamp::with('category')
            ->where('user_id', auth('web')->id());

        if (request()->filled('search')) {
            $query->where('title', 'LIKE', '%'.request('search').'%');
        }

        if (request()->filled('category') && request('category') !== 'all') {
            $category = BootcampCategory::where('slug', request('category'))->first();
            if ($category) {
                $query->where('category_id', $category->id);
            }
        }

        if (request()->filled('status') && request('status') !== 'all') {
            $query->where('status', request('status') === 'active' ? 1 : 0);
        }

        if (request()->filled('instructor') && request('instructor') !== 'all') {
            $query->where('user_id', request('instructor'));
        }

        if (request()->filled('price') && request('price') !== 'all') {
            $price = request('price');
            $column = $price === 'discounted' ? 'discount_flag' : 'is_paid';
            $value = $price === 'free' ? 0 : 1;
            $query->where($column, $value);
        }

        $page_data['bootcamps'] = $query->paginate(20)->appends(request()->query());

        return view('instructor.bootcamp.index', $page_data);
    }

    public function create()
    {
        return view('instructor.bootcamp.create');
    }

    public function edit($id)
    {
        $bootcamp = Bootcamp::where('id', $id)
            ->where('user_id', auth('web')->id())
            ->firstOrFail();

        $page_data['bootcamp_details'] = $bootcamp;
        $page_data['modules'] = BootcampModule::where('bootcamp_id', $id)->get();

        return view('instructor.bootcamp.edit', $page_data);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'required|string',
            'category_id' => 'required|exists:bootcamp_categories,id',
            'is_paid' => Rule::in(['0', '1']),
            'price' => 'required_if:is_paid,1|nullable|numeric|min:1',
            'discount_flag' => Rule::in(['', '1']),
            'discounted_price' => 'required_if:discount_flag,1|nullable|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        if (Bootcamp::where('user_id', auth('web')->id())->where('title', $request->title)->exists()) {
            return back()->with('error', get_phrase('This title has been taken.'))->withInput();
        }

        $data = $request->only([
            'title', 'short_description', 'description', 'category_id',
            'is_paid', 'price', 'discount_flag', 'discounted_price',
        ]);
        $data['user_id'] = auth('web')->id();
        $data['slug'] = slugify($request->title);
        $data['publish_date'] = strtotime($request->publish_date);
        $data['status'] = 1;

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = 'uploads/bootcamp/thumbnail/'.nice_file_name($request->title, $request->thumbnail->extension());
            app(FileUploaderService::class)->upload($request->thumbnail, $data['thumbnail']);
        }

        $insert_id = Bootcamp::insertGetId($data);

        return redirect()->route('instructor.bootcamp.edit', [$insert_id, 'tab' => 'curriculum'])
            ->with('success', get_phrase('Bootcamp has been created.'));
    }

    public function delete($id)
    {
        $bootcamp = Bootcamp::where('id', $id)->where('user_id', auth('web')->id())->firstOrFail();
        $bootcamp->delete();

        return back()->with('success', get_phrase('Bootcamp has been deleted.'));
    }

    public function update(Request $request, $id)
    {
        $bootcamp = Bootcamp::where('id', $id)->where('user_id', auth('web')->id())->firstOrFail();
        $data = [];

        switch ($request->tab) {
            case 'basic':
                $request->validate([
                    'title' => 'required|string',
                    'description' => 'required|string',
                    'category_id' => 'required|exists:bootcamp_categories,id',
                ]);

                if (Bootcamp::where('title', $request->title)->where('id', '!=', $id)->exists()) {
                    return back()->with('error', get_phrase('This title has been taken.'));
                }

                $data = $request->only(['title', 'short_description', 'description', 'category_id']);
                $data['slug'] = slugify($request->title);
                $data['publish_date'] = strtotime($request->publish_date);
                break;

            case 'pricing':
                $request->validate([
                    'is_paid' => Rule::in(['0', '1']),
                    'price' => 'required_if:is_paid,1|nullable|numeric|min:1',
                    'discount_flag' => Rule::in(['', '1']),
                    'discounted_price' => 'required_if:discount_flag,1|nullable|numeric|min:1',
                ]);
                $data = $request->only(['is_paid', 'price', 'discount_flag', 'discounted_price']);
                break;

            case 'info':
                $data['requirements'] = json_encode(array_filter($request->requirements ?? []));
                $data['outcomes'] = json_encode(array_filter($request->outcomes ?? []));

                $faqs = [];
                foreach ($request->faq_title as $key => $title) {
                    if ($title) {
                        $faqs[] = [
                            'title' => $title,
                            'description' => $request->faq_description[$key] ?? '',
                        ];
                    }
                }
                $data['faqs'] = json_encode($faqs);
                break;

            case 'media':
                if ($request->hasFile('thumbnail')) {
                    remove_file($bootcamp->thumbnail);
                    $data['thumbnail'] = 'uploads/bootcamp/thumbnail/'.nice_file_name($request->title, $request->thumbnail->extension());
                    app(FileUploaderService::class)->upload($request->thumbnail, $data['thumbnail']);
                }
                break;

            case 'seo':
                $seo = SeoField::updateOrCreate(
                    ['name_route' => 'bootcamp.details', 'bootcamp_id' => $id],
                    [
                        'route' => 'Bootcamp Details',
                        'meta_title' => $request->meta_title,
                        'meta_description' => $request->meta_description,
                        'meta_keywords' => collect(json_decode($request->meta_keywords, true))->pluck('value')->implode(', '),
                        'meta_robot' => $request->meta_robot,
                        'canonical_url' => $request->canonical_url,
                        'custom_url' => $request->custom_url,
                        'json_ld' => $request->json_ld,
                        'og_title' => $request->og_title,
                        'og_description' => $request->og_description,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                if ($request->hasFile('og_image')) {
                    remove_file($seo->og_image ?? null);
                    $path = 'uploads/seo-og-images/'.$bootcamp->id.'-'.$request->og_image->getClientOriginalName();
                    app(FileUploaderService::class)->upload($request->og_image, $path, 600);
                    $seo->update(['og_image' => $path]);
                }

                return back()->with('success', get_phrase('SEO settings updated.'));
        }

        $bootcamp->update($data);

        return back()->with('success', get_phrase('Bootcamp has been updated successfully.'));
    }

    public function duplicate($id)
    {
        $bootcamp = Bootcamp::where('id', $id)->firstOrFail();

        $newBootcamp = $bootcamp->replicate();
        $newBootcamp->title .= ' copy';
        $newBootcamp->slug = slugify($newBootcamp->title);
        $newBootcamp->user_id = auth('web')->id();
        $newBootcamp->status = 1;
        $newBootcamp->save();

        return redirect()->route('instructor.bootcamp.edit', [$newBootcamp->id, 'tab' => 'basic'])
            ->with('success', get_phrase('Bootcamp has been duplicated.'));
    }

    public function status($id)
    {
        $bootcamp = Bootcamp::where('id', $id)->where('user_id', auth('web')->id())->first();

        if (! $bootcamp) {
            return response()->json(['error' => get_phrase('Data not found.')], 404);
        }

        $bootcamp->update(['status' => ! $bootcamp->status]);

        return response()->json(['success' => get_phrase('Status has been updated.')]);
    }

    public function purchase_history()
    {
        $page_data['purchases'] = BootcampPurchase::join('bootcamps', 'bootcamp_purchases.bootcamp_id', 'bootcamps.id')
            ->where('bootcamps.user_id', auth('web')->id())
            ->select(
                'bootcamp_purchases.*',
                'bootcamps.title',
                'bootcamps.slug',
                'bootcamps.price as amount'
            )
            ->latest('bootcamp_purchases.id')
            ->paginate(20);

        return view('instructor.bootcamp.purchase_history', $page_data);
    }

    public function invoice($id)
    {
        $invoice = BootcampPurchase::join('bootcamps', 'bootcamp_purchases.bootcamp_id', 'bootcamps.id')
            ->where('bootcamps.user_id', auth('web')->id())
            ->where('bootcamp_purchases.id', $id)
            ->select('bootcamp_purchases.*', 'bootcamps.title', 'bootcamps.slug')
            ->first();

        if (! $invoice) {
            return back()->with('error', get_phrase('Data not found.'));
        }

        return view('instructor.bootcamp.invoice', ['invoice' => $invoice]);
    }
}
