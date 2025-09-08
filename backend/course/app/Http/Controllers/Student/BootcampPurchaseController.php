<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Bootcamp;
use App\Models\BootcampPurchase;
use App\Models\OfflinePayment;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class BootcampPurchaseController extends Controller
{
    /**
     * Show bootcamp purchase page or process free bootcamp instantly.
     */
    public function purchase($id)
    {
        $bootcamp = Bootcamp::find($id);

        if (! $bootcamp) {
            Session::flash('error', get_phrase('Data not found.'));

            return redirect()->back();
        }

        // Prevent users from buying their own bootcamp
        if ($bootcamp->user_id == auth('web')->id()) {
            Session::flash('error', get_phrase('You own this item.'));

            return redirect()->back();
        }

        // Prevent duplicate purchases
        if (is_purchased_bootcamp($bootcamp->id)) {
            Session::flash('error', get_phrase('Item is already purchased.'));

            return redirect()->back();
        }

        // If bootcamp is free, enroll instantly
        if ($bootcamp->is_paid == 0) {
            $payment = [
                'invoice' => '#'.Str::random(20),
                'user_id' => auth('web')->id(),
                'bootcamp_id' => $bootcamp->id,
                'price' => 0,
                'tax' => 0,
                'payment_method' => 'free',
                'status' => 1,
                'instructor_revenue' => 0,
                'admin_revenue' => 0,
            ];

            BootcampPurchase::insert($payment);

            Session::flash('success', get_phrase('Enrolled in the bootcamp successfully'));

            return redirect()->route('my.bootcamps');
        }

        // Check if offline payment is already in process
        $processing = OfflinePayment::where([
            'user_id' => auth('web')->id(),
            'items' => $bootcamp->id,
            'item_type' => 'bootcamp',
            'status' => 0,
        ])->first();

        if ($processing) {
            Session::flash('warning', get_phrase('Your request is in process.'));

            return redirect()->back();
        }

        // Prepare payment details
        $discount = $bootcamp->discount_flag ? $bootcamp->discounted_price : 0;
        $price = $bootcamp->price - $discount;

        $payment_details = [
            'items' => [
                [
                    'id' => $bootcamp->id,
                    'title' => $bootcamp->title,
                    'subtitle' => '',
                    'price' => $bootcamp->price,
                    'discount_price' => $discount,
                ],
            ],
            'custom_field' => [
                'item_type' => 'bootcamp',
                'pay_for' => get_phrase('Bootcamp payment'),
            ],
            'success_method' => [
                'model_name' => 'BootcampPurchase',
                'function_name' => 'purchase_bootcamp',
            ],
            'payable_amount' => round($price, 2),
            'tax' => 0,
            'coupon' => null,
            'cancel_url' => route('bootcamp.details', $bootcamp->slug),
            'success_url' => route('payment.success', ''),
        ];

        Session::put(['payment_details' => $payment_details]);

        return redirect()->route('payment');
    }

    /**
     * Show the authenticated user's bootcamp purchase history.
     */
    public function purchase_history()
    {
        $page_data['purchases'] = BootcampPurchase::join('bootcamps', 'bootcamp_purchases.bootcamp_id', '=', 'bootcamps.id')
            ->where('bootcamp_purchases.user_id', auth('web')->id())
            ->select('bootcamp_purchases.*', 'bootcamps.title as bootcamp_title', 'bootcamps.slug as bootcamp_slug')
            ->latest('bootcamp_purchases.id')
            ->paginate(10);

        return view(theme_path().'student.purchase_history.bootcamp', $page_data);
    }

    /**
     * Show the invoice for a bootcamp purchase.
     */
    public function invoice($id)
    {
        $invoice = BootcampPurchase::join('bootcamps', 'bootcamp_purchases.bootcamp_id', '=', 'bootcamps.id')
            ->where('bootcamp_purchases.id', $id)
            ->where('bootcamp_purchases.user_id', auth('web')->id())
            ->select(
                'bootcamp_purchases.*',
                'bootcamps.title as bootcamp_title',
                'bootcamps.slug as bootcamp_slug'
            )
            ->first();

        if (! $invoice) {
            Session::flash('error', get_phrase('Data not found.'));

            return redirect()->back();
        }

        return view(theme_path().'student.purchase_history.bootcamp_invoice', ['invoice' => $invoice]);
    }
}
