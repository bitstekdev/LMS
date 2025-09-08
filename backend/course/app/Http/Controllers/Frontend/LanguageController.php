<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function selectLanguage(Request $request)
    {
        $request->validate([
            'language' => 'required|string|max:10',
        ]);

        session(['language' => strtolower($request->language)]);

        return redirect()->back();
    }
}
