<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\PaymentHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class CoursePurchaseService
{
    /**
     * Handles purchasing one or more courses.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function buyCourses(string $identifier)
    {
        $paymentDetails = session('payment_details');

        if (empty($paymentDetails) || ! isset($paymentDetails['items'])) {
            abort(400, 'Invalid or missing payment details.');
        }

        $transactionKeys = session('keys');
        $transactionId = json_encode($transactionKeys ?? []);
        $invoiceNo = Str::random(10);
        $userId = auth('web')->id();

        DB::beginTransaction();

        try {
            foreach ($paymentDetails['items'] as $item) {
                $course = Course::findOrFail($item['id']);

                $price = $item['price'];
                $taxRate = get_settings('course_selling_tax', 0);
                $instructorRevenueRate = get_settings('instructor_revenue', 0);

                $tax = $price * ($taxRate / 100);
                $adminRevenue = $price;
                $instructorRevenue = null;

                $creator = $course->user;

                if ($creator && $creator->role !== 'admin') {
                    $instructorRevenue = $price * ($instructorRevenueRate / 100);
                    $adminRevenue = $price - $instructorRevenue;
                }

                // Store payment record
                PaymentHistory::create([
                    'course_id' => $course->id,
                    'user_id' => $userId,
                    'amount' => $price,
                    'tax' => $tax,
                    'admin_revenue' => $adminRevenue,
                    'instructor_revenue' => $instructorRevenue,
                    'payment_type' => $identifier,
                    'coupon' => $paymentDetails['custom_field']['coupon_code'] ?? null,
                    'transaction_id' => $transactionId,
                    'invoice' => $invoiceNo,
                ]);

                // Enroll the user
                Enrollment::create([
                    'course_id' => $course->id,
                    'user_id' => $userId,
                    'enrollment_type' => 'paid',
                    'entry_date' => now()->timestamp,
                ]);
            }

            // Delete items from cart
            $cartIds = $paymentDetails['custom_field']['cart_id'] ?? [];

            if (! empty($cartIds)) {
                \App\Models\AddToCart::whereIn('id', $cartIds)->delete();
            }

            Session::forget(['payment_details', 'keys']);
            Session::flash('success_message', 'Courses purchased successfully.');

            DB::commit();

            return redirect($paymentDetails['cancel_url'] ?? '/dashboard');

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Course Purchase Failed: '.$e->getMessage());

            Session::flash('error_message', 'Something went wrong during course purchase.');

            return redirect()->back();
        }
    }

    public function purchase(string $identifier)
    {
        $paymentDetails = session('payment_details');

        $transactionData = [];

        if (Session::has('keys')) {
            $transactionData['transaction_id'] = json_encode(Session::pull('keys'));
        }

        if (Session::has('session_id')) {
            $transactionData['session_id'] = Session::pull('session_id');
        }

        $invoice = Str::random(20);
        $userId = auth('web')->id();
        $removeSessionItems = ['payment_details'];

        foreach ($paymentDetails['items'] as $item) {
            $courseId = $item['id'];
            $price = $item['price'];
            $discountedPrice = $item['discount_price'] ?? null;
            $finalAmount = $discountedPrice ?: $price;

            $course = Course::findOrFail($courseId);
            $creator = $course->creator;

            $adminRevenue = $instructorRevenue = 0;
            if ($creator->role === 'admin') {
                $adminRevenue = $paymentDetails['payable_amount'];
            } else {
                $instructorRevenue = $paymentDetails['payable_amount'] * (get_settings('instructor_revenue') / 100);
                $adminRevenue = $paymentDetails['payable_amount'] - $instructorRevenue;
            }

            // Save payment history
            $payment = PaymentHistory::create([
                'invoice' => $invoice,
                'user_id' => $userId,
                'course_id' => $courseId,
                'amount' => $finalAmount,
                'tax' => $paymentDetails['tax'],
                'admin_revenue' => $adminRevenue,
                'instructor_revenue' => $instructorRevenue,
                'payment_type' => $identifier,
                'coupon' => $paymentDetails['coupon'],
                'transaction_id' => $transactionData['transaction_id'] ?? null,
                'session_id' => $transactionData['session_id'] ?? null,
            ]);

            // If successful, enroll the user
            if ($payment) {
                $giftedUserId = $paymentDetails['custom_field']['gifted_user_id'] ?? null;
                $enrollUserId = $giftedUserId ?: $userId;

                $expiryDate = null;
                if ($course->expiry_period > 0) {
                    $expiryDate = now()->addDays($course->expiry_period * 30);
                }

                Enrollment::create([
                    'course_id' => $courseId,
                    'user_id' => $enrollUserId,
                    'enrollment_type' => 'paid',
                    'entry_date' => now()->timestamp,
                    'expiry_date' => $expiryDate,
                ]);
            }
        }

        // Clean up cart items
        if (! empty($paymentDetails['custom_field']['cart_id'])) {
            $cartIds = $paymentDetails['custom_field']['cart_id'];

            CartItem::where('user_id', $userId)
                ->whereIn('course_id', $cartIds)
                ->delete();
        }

        Session::forget($removeSessionItems);
        Session::flash('success', 'Course enrolled successfully.');

        return redirect()->route('my.courses');
    }
}
