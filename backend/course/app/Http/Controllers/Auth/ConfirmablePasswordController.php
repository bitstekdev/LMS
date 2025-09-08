<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ConfirmablePasswordController extends Controller
{
    /**
     * Show the confirm password view.
     */
    public function show(): View
    {
        return view('auth.confirm-password');
    }

    /**
     * Confirm the user's password and continue.
     */
    public function store(Request $request): RedirectResponse
    {
        // Validate the provided password against the authenticated user's email
        $credentials = [
            'email' => $request->user()->email,
            'password' => $request->password,
        ];

        if (! Auth::guard('web')->validate($credentials)) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        // Set the password confirmation time
        $request->session()->put('auth.password_confirmed_at', time());

        // Redirect to intended URL or fallback to dashboard
        return redirect()->intended(route('dashboard'));
    }
}
