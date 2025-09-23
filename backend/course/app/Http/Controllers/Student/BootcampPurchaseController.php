<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Bootcamp;
use App\Models\BootcampPurchase;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class BootcampPurchaseController extends Controller
{
    /**
     * Enroll student in bootcamp (free or allowed enrollment).
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

        // Create bootcamp enrollment (purchase) regardless of paid/free
        BootcampPurchase::create([
            'invoice' => '#'.Str::random(20),
            'user_id' => auth('web')->id(),
            'bootcamp_id' => $bootcamp->id,
            'price' => $bootcamp->is_paid ? $bootcamp->price : 0,
            'tax' => 0,
            'payment_method' => $bootcamp->is_paid ? 'manual' : 'free',
            'status' => 1,
            'instructor_revenue' => 0,
            'admin_revenue' => 0,
        ]);

        Session::flash('success', get_phrase('Enrolled in the bootcamp successfully'));

        return redirect()->route('my.bootcamps');
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

        return view('frontend.default.student.purchase_history.bootcamp', $page_data);
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

        return view('frontend.default.student.purchase_history.bootcamp_invoice', ['invoice' => $invoice]);
    }
}
