<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\View\View as ViewResponse;
use Throwable;

class StaticPageController extends Controller
{
    /**
     * Show the About Us page.
     */
    public function about(): ViewResponse
    {
        return $this->renderStaticPage('about_us');
    }

    /**
     * Show the Privacy Policy page.
     */
    public function privacy(): ViewResponse
    {
        return $this->renderStaticPage('privacy_policy');
    }

    /**
     * Show the Refund Policy page.
     */
    public function refund(): ViewResponse
    {
        return $this->renderStaticPage('refund_policy');
    }

    /**
     * Show the FAQ page.
     */
    public function faq(): ViewResponse
    {
        return $this->renderStaticPage('faq');
    }

    /**
     * Show the Terms & Conditions page.
     */
    public function terms(): ViewResponse
    {
        return $this->renderStaticPage('terms_and_condition');
    }

    /**
     * Show the Cookie Policy page.
     */
    public function cookie(): ViewResponse
    {
        return $this->renderStaticPage('cookie_policy');
    }

    /**
     * Shared view loader for static pages.
     */
    private function renderStaticPage(string $page): ViewResponse
    {
        $theme = get_frontend_settings('theme');
        $viewPath = "frontend.{$theme}.static.{$page}";

        try {
            if (! View::exists($viewPath)) {
                Log::warning("Static page view not found: {$viewPath}");
                abort(404, 'Page not found');
            }

            return view($viewPath);
        } catch (Throwable $e) {
            Log::error("Error rendering static page [{$page}] at [{$viewPath}]: ".$e->getMessage());
            abort(500, 'An error occurred while loading the page.');
        }
    }
}
