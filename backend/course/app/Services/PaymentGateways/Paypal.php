<?php

namespace App\Services\PaymentGateways;

use App\Models\PaymentGateway;
use Illuminate\Support\Facades\Log;
use Throwable;

class Paypal
{
    /**
     * Check PayPal payment status using REST API.
     */
    public static function payment_status(string $identifier, array $transaction_keys = []): bool
    {
        try {
            $paymentGateway = PaymentGateway::where('identifier', $identifier)->firstOrFail();
            $keys = $paymentGateway->keys;

            $isSandbox = $paymentGateway->test_mode === 1;

            $clientId = $isSandbox ? $keys['sandbox_client_id'] : $keys['production_client_id'];
            $secretKey = $isSandbox ? $keys['sandbox_secret_key'] : $keys['production_secret_key'];
            $paypalURL = $isSandbox ? 'https://api.sandbox.paypal.com/v1/' : 'https://api.paypal.com/v1/';

            // Step 1: Get access token
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $paypalURL.'oauth2/token',
                CURLOPT_HEADER => false,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_USERPWD => "{$clientId}:{$secretKey}",
                CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
            ]);

            $authResponse = curl_exec($ch);

            if (curl_errno($ch)) {
                Log::error('PayPal OAuth cURL error: '.curl_error($ch));
                curl_close($ch);

                return false;
            }

            curl_close($ch);

            if (empty($authResponse)) {
                Log::error('PayPal OAuth response was empty.');

                return false;
            }

            $authData = json_decode($authResponse);

            if (! isset($authData->access_token)) {
                Log::error('PayPal OAuth failed to retrieve access token.', [
                    'response' => $authResponse,
                ]);

                return false;
            }

            // Step 2: Verify transaction
            $paymentId = $transaction_keys['payment_id'] ?? null;

            if (! $paymentId) {
                Log::warning('PayPal transaction key (payment_id) not provided.', [
                    'transaction_keys' => $transaction_keys,
                ]);

                return false;
            }

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $paypalURL."checkout/orders/{$paymentId}",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HEADER => false,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer '.$authData->access_token,
                    'Accept: application/json',
                    'Content-Type: application/json',
                ],
            ]);

            $orderResponse = curl_exec($curl);

            if (curl_errno($curl)) {
                Log::error('PayPal order lookup cURL error: '.curl_error($curl));
                curl_close($curl);

                return false;
            }

            curl_close($curl);

            $result = json_decode($orderResponse);

            if (isset($result->status) && in_array($result->status, ['approved', 'COMPLETED'])) {
                Log::info('PayPal payment verified successfully.', [
                    'payment_id' => $paymentId,
                    'status' => $result->status,
                ]);

                return true;
            }

            Log::warning('PayPal payment not approved.', [
                'payment_id' => $paymentId,
                'status' => $result->status ?? 'unknown',
                'response' => $result,
            ]);

            return false;
        } catch (Throwable $e) {
            Log::error('Paypal::payment_status() error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'identifier' => $identifier,
                'transaction_keys' => $transaction_keys,
            ]);

            return false;
        }
    }
}
