<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    /**
     * Set the application language in session.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function select_lng(Request $request)
    {
        // Sanitize and lowercase the language input
        $language = strtolower(trim($request->language));

        // Optionally, validate supported languages if needed
        // Example: ['en', 'fr', 'es']
        // if (!in_array($language, config('app.supported_languages'))) {
        //     return redirect()->back()->with('error', 'Unsupported language');
        // }

        session(['language' => $language]);

        return redirect()->back();
    }
}
