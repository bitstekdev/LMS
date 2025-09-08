<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerifyEmailController extends Controller
{
    /**
     * Handle email verification links without requiring an authenticated session.
     *
     * Expects route parameters: {id} and {hash}.
     * The route should be protected by the "signed" and "throttle" middleware.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        // Resolve the user from the signed URL
        $user = User::findOrFail((int) $request->route('id'));

        // Ensure the provided hash matches the user's email hash
        $expected = sha1($user->getEmailForVerification());
        if (! hash_equals((string) $request->route('hash'), $expected)) {
            abort(403, 'Invalid or expired verification link.');
        }

        // Mark as verified if not already
        if (! $user->hasVerifiedEmail()) {
            if ($user->markEmailAsVerified()) {
                event(new Verified($user));
            }
        }

        // Optionally log the user in so they land in an authenticated state
        Auth::login($user);

        // Redirect to intended location (if any) or home, preserving the "verified" flag
        return redirect()->intended(route('home').'?verified=1');
    }
}
