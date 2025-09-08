<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Throwable;

class PasswordController extends Controller
{
    /**
     * Update the authenticated user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        // Validation uses Laravel's built-in `current_password` rule to verify the old password.
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password:web'],
            'password' => ['required', PasswordRule::defaults(), 'confirmed'],
        ]);

        try {
            $user = $request->user();
            if (! $user) {
                return back()->withErrors(['updatePassword' => __('You must be logged in to update your password.')]);
            }

            $user->update([
                'password' => Hash::make($validated['password']),
            ]);

            // Optional: you could also regenerate the session here if desired.
            // $request->session()->regenerate();

            return back()->with('status', 'password-updated');
        } catch (Throwable $e) {
            Log::error('Password update failed: '.$e->getMessage());

            return back()
                ->withInput($request->except(['password', 'password_confirmation', 'current_password']))
                ->withErrors(['updatePassword' => __('Unable to update password right now. Please try again.')]);
        }
    }
}
