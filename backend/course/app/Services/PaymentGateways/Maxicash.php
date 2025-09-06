<?php

namespace App\Services\PaymentGateways;

use App\Models\PaymentGateway;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Throwable;

class Maxicash
{
    /**
     * Handle Maxicash payment status webhook or callback.
     */
    public static function payment_status(string $identifier, array $transaction_keys = []): bool
    {
        try {
            if (! empty($transaction_keys)) {
                // Remove the first key if it's a token or redundant
                array_shift($transaction_keys);

                // Store remaining keys in session
                Session::put('keys', $transaction_keys);

                Log::info('Maxicash payment keys processed.', [
                    'identifier' => $identifier,
                    'keys' => $transaction_keys,
                ]);

                return true;
            }

            Log::warning('Maxicash payment_status called with empty transaction keys.', [
                'identifier' => $identifier,
            ]);

            return false;
        } catch (Throwable $e) {
            Log::error('Maxicash::payment_status() error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'identifier' => $identifier,
                'transaction_keys' => $transaction_keys,
            ]);

            return false;
        }
    }

    /**
     * Create a new Maxicash payment URL and return it.
     */
    public static function payment_create(string $identifier): ?string
    {
        try {
            $paymentGateway = PaymentGateway::where('identifier', 'maxicash')->firstOrFail();
            $user = auth('web')->user();
            $paymentDetails = session('payment_details');
            $keys = $paymentGateway->keys;

            // Build products string
            $productsName = collect($paymentDetails['items'] ?? [])
                ->pluck('title')
                ->implode(', ');

            // Prepare payload for Maxicash
            $payload = [
                'PayType' => 'maxicash',
                'MerchantID' => $keys['merchant_id'],
                'MerchantPassword' => $keys['merchant_password'],
                'Amount' => (string) round($paymentDetails['payable_amount'] * 100),
                'Currency' => $paymentGateway->currency,
                'Telephone' => $user->phone,
                'Language' => 'en',
                'Reference' => 'MAXI_TXN_'.uniqid(),
                'accepturl' => "{$paymentDetails['success_url']}/{$paymentGateway->identifier}",
                'declineurl' => $paymentDetails['cancel_url'],
                'cancelurl' => $paymentDetails['cancel_url'],
                'notifyurl' => $paymentDetails['cancel_url'], // Can be replaced with actual webhook
            ];

            $data = json_encode($payload);

            // Choose environment endpoint
            $url = $paymentGateway->test_mode
                ? 'https://api-testbed.maxicashapp.com/payentry'
                : 'https://api.maxicashapp.com/payentry';

            $fullUrl = "{$url}?data={$data}";

            Log::info('Maxicash payment URL generated.', [
                'url' => $fullUrl,
                'payload' => $payload,
            ]);

            return $fullUrl;
        } catch (Throwable $e) {
            Log::error('Maxicash::payment_create() error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'identifier' => $identifier,
            ]);

            return null;
        }
    }
}
