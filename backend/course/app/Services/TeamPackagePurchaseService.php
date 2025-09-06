<?php

namespace App\Services;

use App\Models\TeamPackagePurchase;
use App\Models\TeamTrainingPackage;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class TeamPackagePurchaseService
{
    public function purchase(string $identifier)
    {
        $paymentDetails = session('payment_details');
        $transactionData = [];
        $removeSessionItems = ['payment_details'];

        if (Session::has('keys')) {
            $transactionData['payment_details'] = json_encode(Session::pull('keys'));
            $removeSessionItems[] = 'keys';
        }

        if (Session::has('session_id')) {
            $transactionData['payment_details'] = Session::pull('session_id');
            $removeSessionItems[] = 'session_id';
        }

        $user = auth('web')->user();
        $packageId = $paymentDetails['items'][0]['id'];
        $package = TeamTrainingPackage::findOrFail($packageId);

        $amount = $paymentDetails['payable_amount'];
        $adminRevenue = $instructorRevenue = 0;

        if (get_user_info($package->user_id)->role === 'admin') {
            $adminRevenue = $amount;
        } else {
            $instructorRevenue = $amount * (get_settings('instructor_revenue') / 100);
            $adminRevenue = $amount - $instructorRevenue;
        }

        TeamPackagePurchase::create([
            'invoice' => '#'.Str::random(20),
            'user_id' => $user->id,
            'package_id' => $packageId,
            'price' => $amount,
            'tax' => $paymentDetails['tax'],
            'payment_method' => $identifier,
            'payment_details' => $transactionData['payment_details'] ?? null,
            'status' => 1,
            'admin_revenue' => $adminRevenue,
            'instructor_revenue' => $instructorRevenue,
        ]);

        Session::forget($removeSessionItems);
        Session::flash('success', get_phrase('Team package purchased successfully.'));

        return redirect()->route('my.team.packages');
    }
}
