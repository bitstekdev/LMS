<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function payment(Request $request, $token)
    {
        if ($request->user()) {
            Auth::login($request->user());
        }

        if ($request->has('app_url')) {
            session(['app_url' => $request->app_url.'://']);
        }

        return redirect()->route('payment');
    }

    public function token(Request $request)
    {
        return response()->json(['token' => csrf_token()]);
    }
}
