<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PayoutSettingsController extends Controller
{
    /**
     * Show the payout settings page for the authenticated instructor.
     */
    public function payout_setting()
    {
        $user = auth('web')->user();

        // paymentkeys is already cast to array in User model
        $page_data['instructor'] = $user->paymentkeys ?? [];

        return view('instructor.payout_setting.index', $page_data);
    }

    /**
     * Store the updated payout settings.
     */
    public function payout_setting_store(Request $request)
    {
        $request->validate([
            'gateways' => 'required|array',
        ]);

        $user = auth('web')->user();

        // Directly save the array, Laravel will handle JSON casting
        $user->update([
            'paymentkeys' => $request->gateways,
        ]);

        return redirect()
            ->route('instructor.payout.setting')
            ->with('success', get_phrase('Payout setting updated'));
    }
}
