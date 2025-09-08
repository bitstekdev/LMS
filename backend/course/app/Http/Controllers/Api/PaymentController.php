<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    /**
     * Bridge API-authenticated user into web guard and redirect to payment page.
     */
    public function payment(Request $request, $token)
    {
        $user = $request->user();

        if ($user) {
            // ✅ Switch the Sanctum user into web session
            Auth::guard('web')->login($user);
        }

        if ($request->has('app_url')) {
            // Store app_url in session for later callbacks
            session(['app_url' => rtrim($request->app_url, ':/').'://']);
        }

        return redirect()->route('payment');
    }

    /**
     * Return a CSRF token for frontend form submissions.
     * If you want an API auth token instead, issue Sanctum tokens here.
     */
    public function token(Request $request)
    {
        return response()->json(['token' => csrf_token()]);
    }
}
