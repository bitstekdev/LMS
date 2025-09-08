<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class PublicFormController extends Controller
{
    /**
     * Show the contact form page.
     */
    public function contactForm()
    {
        $viewPath = 'frontend.'.get_frontend_settings('theme').'.public_form.contact_us';

        return view($viewPath);
    }

    /**
     * Handle contact form submission.
     */
    public function submitContact(Request $request)
    {
        if (
            get_frontend_settings('recaptcha_status') &&
            ! check_recaptcha($request->input('g-recaptcha-response'))
        ) {
            Session::flash('error', get_phrase('Recaptcha verification failed'));

            return redirect()->route('contact.us');
        }

        if (Contact::where('email', $request->email)->exists()) {
            Session::flash('error', get_phrase('This email has been taken.'));

            return redirect()->back();
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        Contact::create($request->only([
            'name', 'email', 'phone', 'address', 'message',
        ]));

        Session::flash('success', get_phrase('Your record has been saved.'));

        return redirect()->back();
    }

    /**
     * Handle newsletter subscription.
     */
    public function submitNewsletter(Request $request)
    {
        if (
            get_frontend_settings('recaptcha_status') &&
            ! check_recaptcha($request->input('g-recaptcha-response'))
        ) {
            Session::flash('error', get_phrase('Recaptcha verification failed'));

            return redirect()->back();
        }

        $request->validate([
            'email' => 'required|email|unique:newsletter_subscribers,email',
        ]);

        if (NewsletterSubscriber::where('email', $request->email)->exists()) {
            Session::flash('error', get_phrase('You have already subscribed.'));

            return redirect()->back();
        }

        NewsletterSubscriber::create([
            'email' => $request->email,
        ]);

        Session::flash('success', get_phrase('You have successfully subscribed.'));

        return redirect()->back();
    }
}
