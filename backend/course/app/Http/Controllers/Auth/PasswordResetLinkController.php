<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;
use Throwable;

class PasswordResetLinkController extends Controller
{
    /**
     * Show the "forgot password" form.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Send a password reset link to the given email.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        try {
            $status = Password::sendResetLink([
                'email' => $validated['email'],
            ]);

            return $status === Password::RESET_LINK_SENT
                ? back()->with('status', __($status))
                : back()
                    ->withInput($request->only('email'))
                    ->withErrors(['email' => __($status)]);
        } catch (Throwable $e) {
            Log::error('Password reset link error: '.$e->getMessage());

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => __('Unable to send reset link. Please try again later.')]);
        }
    }
}
