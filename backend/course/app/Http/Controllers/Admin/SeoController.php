<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeoField;
use App\Services\FileUploaderService;
use Illuminate\Http\Request;

class SeoController extends Controller
{
    public function seo_settings($active_tab = '')
    {
        $page_data = [];

        $page_data['seo_meta_tags'] = SeoField::whereNull('course_id')
            ->whereNull('blog_id')
            ->whereNull('bootcamp_id')
            ->get();

        $page_data['active_tab'] = ! empty($active_tab) ? slugify($active_tab) : 'home';

        return view('admin.setting.seo_setting', $page_data);
    }

    public function seo_settings_update(Request $request, $route = '')
    {
        if (empty($request->all())) {
            return redirect()->back()->with('error', 'SEO update failed. Request was empty.');
        }

        $updateSeo = SeoField::where('route', $route)->first();

        if (! $updateSeo) {
            return redirect()->back()->with('error', 'SEO record not found.');
        }

        // Update SEO fields
        $updateSeo->meta_title = $request->meta_title ?? '';
        $updateSeo->meta_keywords = $request->meta_keywords ?? '';
        $updateSeo->meta_description = $request->meta_description ?? '';
        $updateSeo->meta_robot = $request->meta_robot ?? '';
        $updateSeo->canonical_url = $request->canonical_url ?? '';
        $updateSeo->custom_url = $request->custom_url ?? '';
        $updateSeo->og_title = $request->og_title ?? '';
        $updateSeo->og_description = $request->og_description ?? '';
        $updateSeo->json_ld = $request->json_ld ?? '';

        // Handle OG Image
        if ($request->hasFile('og_image')) {
            $originalFileName = $updateSeo->id.'-'.$request->og_image->getClientOriginalName();
            $destinationPath = 'uploads/seo-og-images/'.$originalFileName;

            // Upload the file
            app(FileUploaderService::class)->upload($request->og_image, $destinationPath, 600);

            // Remove old image if exists
            if (! empty($updateSeo->og_image)) {
                remove_file($updateSeo->og_image);
            }

            $updateSeo->og_image = $destinationPath;
        }

        $updateSeo->save();

        return redirect('/admin/seo_settings/'.$route)->with('success', 'SEO updated successfully.');
    }
}
