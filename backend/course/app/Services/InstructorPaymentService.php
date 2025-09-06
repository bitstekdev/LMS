<?php

namespace App\Services;

use App\Models\Payout;
use Illuminate\Support\Facades\Session;

class InstructorPaymentService
{
    public static function handle(string $identifier)
    {
        $paymentDetails = session('payment_details');

        $payoutId = $paymentDetails['custom_field']['payout_id'] ?? null;
        if (! $payoutId) {
            Session::flash('error_message', 'Payout ID is missing.');

            return redirect($paymentDetails['cancel_url'] ?? '/');
        }

        Payout::where('id', $payoutId)->update([
            'status' => 1,
            'payment_type' => $identifier,
        ]);

        Session::flash('success_message', 'Instructor payment successfully.');

        return redirect($paymentDetails['cancel_url'] ?? '/');
    }
}
