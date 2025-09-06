<?php

namespace App\Services\PaymentGateways;

use App\Models\PaymentGateway;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class Paystack
{
    /**
     * Verifies a Paystack payment status using transaction reference.
     */
    public static function payment_status(string $identifier): bool
    {
        try {
            $reference = request()->query('reference');

            if (empty($reference)) {
                Log::warning('Paystack: Missing transaction reference.');

                return false;
            }

            $paymentGateway = PaymentGateway::where('identifier', $identifier)->firstOrFail();
            $keys = $paymentGateway->keys;

            $secretKey = $paymentGateway->test_mode
                ? $keys['secret_test_key']
                : $keys['secret_live_key'];

            // Verify payment with Paystack API
            $response = Http::withToken($secretKey)
                ->acceptJson()
                ->get("https://api.paystack.co/transaction/verify/{$reference}");

            if ($response->failed()) {
                Log::error('Paystack verification failed.', [
                    'reference' => $reference,
                    'response' => $response->body(),
                ]);

                return false;
            }

            $data = $response->json();

            // Check for successful status
            if (($data['status'] ?? false) && ($data['data']['status'] ?? '') === 'success') {
                Log::info('Paystack payment verified successfully.', [
                    'reference' => $reference,
                    'amount' => $data['data']['amount'] ?? null,
                ]);

                return true;
            }

            Log::warning('Paystack transaction not marked as success.', [
                'reference' => $reference,
                'status' => $data['data']['status'] ?? 'unknown',
            ]);

            return false;
        } catch (Throwable $e) {
            Log::error('Paystack::payment_status() error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'identifier' => $identifier,
            ]);

            return false;
        }
    }
}
