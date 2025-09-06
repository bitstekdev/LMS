<?php

namespace App\Services\PaymentGateways;

use App\Models\PaymentGateway;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Razorpay\Api\Api;
use Throwable;

class Razorpay
{
    /**
     * Verify Razorpay payment status.
     */
    public static function payment_status(string $identifier, array $transaction_keys = []): bool
    {
        try {
            $paymentGateway = PaymentGateway::where('identifier', $identifier)->firstOrFail();
            $keys = $paymentGateway->keys;

            $paymentId = $transaction_keys['razorpay_payment_id'] ?? null;

            if (! $paymentId) {
                Log::warning('Razorpay payment_id is missing.', compact('transaction_keys', 'identifier'));

                return false;
            }

            $auth = base64_encode("{$keys['public_key']}:{$keys['secret_key']}");

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => "https://api.razorpay.com/v1/payments/{$paymentId}",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    "Authorization: Basic {$auth}",
                ],
            ]);

            $response = curl_exec($curl);
            $error = curl_error($curl);
            curl_close($curl);

            if ($error) {
                Log::error('Razorpay API error', ['error' => $error]);

                return false;
            }

            $data = json_decode($response);

            if (isset($data->status) && in_array($data->status, ['captured', 'success'])) {
                Log::info('Razorpay payment captured.', [
                    'payment_id' => $paymentId,
                    'status' => $data->status,
                ]);

                return true;
            }

            Log::warning('Razorpay payment not captured.', [
                'payment_id' => $paymentId,
                'response' => $data,
            ]);

            return false;
        } catch (Throwable $e) {
            Log::error('Razorpay::payment_status() exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'identifier' => $identifier,
            ]);

            return false;
        }
    }

    /**
     * Create a Razorpay order and return checkout data.
     */
    public static function payment_create(string $identifier): ?array
    {
        try {
            $paymentDetails = session('payment_details');
            $user = auth('web')->user();
            $paymentGateway = PaymentGateway::where('identifier', $identifier)->firstOrFail();

            $keys = $paymentGateway->keys;

            // Handle instructor-specific override
            if (($paymentDetails['success_method']['model_name'] ?? '') === 'InstructorPayment') {
                $instructorId = $paymentDetails['items'][0]['id'] ?? null;

                $instructor = User::find($instructorId);
                if ($instructor && ! empty($instructor->paymentkeys['razorpay'])) {
                    $keys = $instructor->paymentkeys['razorpay'];
                }
            }

            $publicKey = $keys['public_key'];
            $secretKey = $keys['secret_key'];
            $currency = $paymentGateway->currency;
            $amount = round($paymentDetails['payable_amount'] * 100, 2);

            $api = new Api($publicKey, $secretKey);
            $order = $api->order->create([
                'receipt' => Str::random(20),
                'amount' => $amount,
                'currency' => $currency,
            ]);

            $pageData = [
                'order_id' => $order['id'],
                'razorpay_id' => $publicKey,
                'amount' => $amount,
                'name' => $user->name,
                'currency' => $currency,
                'email' => $user->email,
                'phone' => $user->phone,
                'address' => $user->address,
                'description' => $paymentDetails['custom_field']['description'] ?? '',
            ];

            return [
                'page_data' => $pageData,
                'color' => null,
                'payment_details' => $paymentDetails,
            ];
        } catch (Throwable $e) {
            Log::error('Razorpay::payment_create() error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'identifier' => $identifier,
            ]);

            return null;
        }
    }
}
