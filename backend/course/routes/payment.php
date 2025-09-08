<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

// ⚙️ Routes requiring authentication
Route::middleware('auth')->controller(PaymentController::class)->group(function () {
    // 💳 Core Payment Routes
    Route::get('payment', 'index')->name('payment');
    Route::get('payment/show_payment_gateway_by_ajax/{identifier}', 'show_payment_gateway_by_ajax')
        ->name('payment.show_payment_gateway_by_ajax');
    Route::any('payment/success/{identifier?}', 'payment_success')->name('payment.success');
    Route::get('payment/create/{identifier}', 'payment_create')->name('payment.create');

    // 🪙 Razorpay
    Route::post('payment/{identifier}/order', 'payment_razorpay')->name('razorpay.order');

    // 💵 Paytm
    Route::get('payment/make/paytm/order', 'make_paytm_order')->name('make.paytm.order');
    Route::get('payment/make/{identifier}/status', 'paytm_paymentCallback')->name('payment.status');

    // 🧾 DOKU
    Route::post('payment/doku_checkout/{identifier}', 'doku_checkout')->name('payment.doku_checkout');
});

// 📣 Webhook or async notifications
Route::any('payment-notification/{identifier?}', [PaymentController::class, 'payment_notification'])
    ->name('payment.notification');

// 📥 Mobile Payment Redirect
Route::get('payment/web_redirect_to_pay_fee', [PaymentController::class, 'webRedirectToPayFee'])
    ->name('payment.web_redirect_to_pay_fee');
