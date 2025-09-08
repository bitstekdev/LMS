<?php

namespace App\Http\Controllers;

use Anand\LaravelPaytmWallet\Facades\PaytmWallet;
use App\Models\PaymentGateway;
use App\Models\PaymentHistory;
use App\Services\PaymentGateways\Doku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class PaymentController extends Controller
{
    public function index()
    {
        $payment_details = session('payment_details');

        if (! $payment_details || ! is_array($payment_details) || count($payment_details) <= 0) {
            Session::flash('error', get_phrase('Payment not configured yet'));

            return redirect()->back();
        }

        if ($payment_details['payable_amount'] <= 0) {
            Session::flash('error', get_phrase('Payable amount cannot be less than 1'));

            return redirect()->to($payment_details['cancel_url']);
        }

        return view('payment.index', [
            'payment_details' => $payment_details,
            'payment_gateways' => PaymentGateway::active()->get(),
        ]);
    }

    public function show_payment_gateway_by_ajax($identifier)
    {
        return view('payment.'.$identifier.'.index', [
            'payment_details' => session('payment_details'),
            'payment_gateway' => PaymentGateway::where('identifier', $identifier)->firstOrFail(),
        ]);
    }

    public function payment_success(Request $request, $identifier = '')
    {
        $payment_details = session('payment_details');
        $payment_gateway = PaymentGateway::where('identifier', $identifier)->firstOrFail();

        $model_name = $payment_gateway->model_name;
        $model_full_path = 'App\\Services\\PaymentGateway\\'.trim($model_name);

        $status = $model_full_path::payment_status($identifier, $request->all());

        if ($status === true) {
            $success_model = $payment_details['success_method']['model_name'];
            $success_function = $payment_details['success_method']['function_name'];
            $success_model_path = 'App\\Services\\PaymentGateway\\'.trim($success_model);

            return $success_model_path::$success_function($identifier);
        }

        if ($status === 'submitted') {
            Session::flash('success', get_phrase('Your payment submitted. It will take some time to enrol.'));

            return redirect(route('home'));
        }

        Session::flash('error', get_phrase('Payment failed! Please try again.'));

        return redirect()->to($payment_details['cancel_url']);
    }

    public function payment_create($identifier)
    {
        $payment_gateway = PaymentGateway::where('identifier', $identifier)->firstOrFail();
        $model_name = $payment_gateway->model_name;
        $model_full_path = 'App\\Services\\PaymentGateway\\'.trim($model_name);

        $created_payment_link = $model_full_path::payment_create($identifier);

        return redirect()->to($created_payment_link);
    }

    public function payment_notification(Request $request, $identifier)
    {
        if ($identifier == 'doku') {
            Doku::payment_status($identifier, $request->all(), $request->headers->all());
        }

        return response()->json(['message' => 'Notification received.']);
    }

    public function payment_razorpay($identifier)
    {
        $payment_gateway = PaymentGateway::where('identifier', $identifier)->firstOrFail();
        $model_name = $payment_gateway->model_name;
        $model_full_path = 'App\\Services\\PaymentGateway\\'.trim($model_name);

        $data = $model_full_path::payment_create($identifier);

        return view('payment.razorpay.payment', compact('data'));
    }

    public function make_paytm_order(Request $request)
    {
        return view('payment.paytm.paytm_merchant_checkout');
    }

    public function paytm_paymentCallback()
    {
        $transaction = PaytmWallet::with('receive');
        $order_id = $transaction->getOrderId();
        $paytm = PaymentHistory::where('session_id', $order_id)->first();

        if (! $paytm) {
            return redirect(route('initiate.payment'))->with('message', 'Order not found.');
        }

        if ($transaction->isSuccessful()) {
            $paytm->update(['status' => 1, 'transaction_id' => $transaction->getTransactionId()]);

            return redirect(route('initiate.payment'))->with('message', 'Your payment is successful.');
        }

        if ($transaction->isFailed()) {
            $paytm->update(['status' => 0, 'transaction_id' => $transaction->getTransactionId()]);

            return redirect(route('initiate.payment'))->with('message', 'Your payment has failed.');
        }

        if ($transaction->isOpen()) {
            $paytm->update(['status' => 2, 'transaction_id' => $transaction->getTransactionId()]);

            return redirect(route('initiate.payment'))->with('message', 'Your payment is processing.');
        }

        return redirect(route('initiate.payment'))->with('message', $transaction->getResponseMessage() ?? 'Unknown error');
    }

    public function doku_checkout($identifier)
    {
        $payment_gateway = PaymentGateway::where('identifier', $identifier)->firstOrFail();
        $keys = $payment_gateway->keys;
        $test_mode = $payment_gateway->isTestMode();
        $user = auth('web')->user();
        $payment_details = session('payment_details');

        $product_title = $payment_details['items'][0]['title'];
        $amount = $payment_details['items'][0]['price'];
        $currency = $payment_gateway->currency;

        Doku::storeTempData();

        $requestBody = [
            'order' => [
                'amount' => $amount,
                'invoice_number' => 'INV-'.rand(1, 10000),
                'currency' => $currency,
                'callback_url' => $payment_details['success_url']."/$identifier",
                'line_items' => [[
                    'name' => $product_title,
                    'price' => $amount,
                    'quantity' => 1,
                ]],
            ],
            'payment' => ['payment_due_date' => 60],
            'customer' => [
                'id' => 'CUST-'.rand(1, 1000),
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'address' => $user->address,
                'country' => 'ID',
            ],
        ];

        $requestId = rand(1, 100000);
        $dateTimeFinal = gmdate('Y-m-d\TH:i:s\Z');
        $targetPath = '/checkout/v1/payment';
        $baseUrl = $test_mode ? 'https://api-sandbox.doku.com' : 'https://api.doku.com';

        $digestValue = base64_encode(hash('sha256', json_encode($requestBody), true));
        $componentSignature = "Client-Id:{$keys['client_id']}\nRequest-Id:$requestId\nRequest-Timestamp:$dateTimeFinal\nRequest-Target:$targetPath\nDigest:$digestValue";
        $signature = base64_encode(hash_hmac('sha256', $componentSignature, $test_mode ? $keys['secret_test_key'] : $keys['secret_live_key'], true));

        $headers = [
            'Content-Type: application/json',
            'Client-Id:'.$keys['client_id'],
            'Request-Id:'.$requestId,
            'Request-Timestamp:'.$dateTimeFinal,
            'Signature: HMACSHA256='.$signature,
        ];

        $ch = curl_init($baseUrl.$targetPath);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestBody));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $responseJson = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 200 && is_string($responseJson)
            ? json_decode($responseJson, true)
            : null;
    }

    public function webRedirectToPayFee(Request $request)
    {
        if (! $request->has('auth')) {
            return redirect()->route('login')->withErrors(['email' => 'Authentication token is missing.']);
        }

        $base64Credentials = substr($request->query('auth'), 6);
        $credentials = base64_decode($base64Credentials);
        [$email, $password, $timestamp] = explode(':', $credentials);

        $difference = time() - (int) $timestamp;

        if ($difference < 86400) {
            // 👇 Use session login explicitly
            if (Auth::guard('web')->attempt(['email' => $email, 'password' => $password])) {
                return redirect()->route('cart');
            }
        }

        return redirect()->route('login')->withErrors([
            'email' => $difference >= 86400 ? 'Token expired!' : 'Invalid email or password',
        ]);
    }
}
