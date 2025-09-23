<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Contact;

class PublicFormController extends Controller
{
    /**
     * Show the contact form page.
     */
    public function contactForm()
    {
        $viewPath = 'frontend.default.public_form.contact_us';

        return view($viewPath);
    }
}
