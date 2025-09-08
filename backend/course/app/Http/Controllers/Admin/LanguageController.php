<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    /**
     * Update the current session language.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function select_lng(Request $request)
    {
        $validated = $request->validate([
            'language' => 'required|string|min:2|max:10',
        ]);

        session(['language' => strtolower($request->language)]);

        return redirect()->back();
    }
}
