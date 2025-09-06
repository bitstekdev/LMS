<?php

namespace App\Services\PaymentGateways;

use App\Models\PaymentGateway;
use Illuminate\Support\Facades\Log;
use Throwable;

class Sslcommerz
{
    /**
     * Check the status of a payment via SSLCommerz Validator API.
     */
    public static function payment_status(string $identifier, array $transaction_keys = []): bool
    {
        try {
            $paymentGateway = PaymentGateway::where('identifier', $identifier)->firstOrFail();
            $keys = $paymentGateway->keys;

            $valId = urlencode(request()->post('val_id'));
            $testMode = $paymentGateway->test_mode;

            $storeKey = $testMode ? $keys['store_key'] : $keys['store_live_key'];
            $storePassword = $testMode ? $keys['store_password'] : $keys['store_live_password'];
            $baseUrl = $testMode ? 'https://sandbox.sslcommerz.com' : 'https://securepay.sslcommerz.com';

            $validationUrl = "{$baseUrl}/validator/api/validationserverAPI.php?val_id={$valId}&store_id={$storeKey}&store_passwd={$storePassword}&v=1&format=json";

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $validationUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);

            $response = curl_exec($curl);
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if ($code === 200 && $response !== false) {
                $result = json_decode($response, true);

                if (in_array($result['status'] ?? '', ['VALID', 'VALIDATED'])) {
                    Log::info('SSLCommerz payment validated', [
                        'val_id' => $valId,
                        'identifier' => $identifier,
                    ]);

                    return true;
                }

                Log::warning('SSLCommerz payment failed validation', [
                    'response' => $result,
                ]);
            }

            return false;
        } catch (Throwable $e) {
            Log::error('SSLCommerz::payment_status() error', [
                'message' => $e->getMessage(),
                'identifier' => $identifier,
            ]);

            return false;
        }
    }

    /**
     * Create a payment session with SSLCommerz.
     */
    public static function payment_create(string $identifier): ?string
    {
        try {
            $paymentDetails = session('payment_details');
            $user = auth('web')->user();
            $paymentGateway = PaymentGateway::where('identifier', 'sslcommerz')->firstOrFail();
            $keys = $paymentGateway->keys;

            $testMode = $paymentGateway->test_mode;

            $storeKey = $testMode ? $keys['store_key'] : $keys['store_live_key'];
            $storePassword = $testMode ? $keys['store_password'] : $keys['store_live_password'];
            $apiUrl = $testMode
                ? 'https://sandbox.sslcommerz.com/gwprocess/v3/api.php'
                : 'https://securepay.sslcommerz.com/gwprocess/v3/api.php';

            $transactionId = 'SSLCZ_TXN_'.uniqid();

            $postData = [
                'user_id' => $user->id,
                'payment_type' => $identifier,
                'items_id' => $paymentDetails['items'][0]['id'] ?? null,
                'store_id' => $storeKey,
                'store_passwd' => $storePassword,
                'total_amount' => round($paymentDetails['payable_amount']),
                'currency' => 'BDT',
                'tran_id' => $transactionId,
                'success_url' => "{$paymentDetails['success_url']}/{$paymentGateway->identifier}",
                'fail_url' => $paymentDetails['cancel_url'],
                'cancel_url' => $paymentDetails['cancel_url'],

                // Customer Info
                'cus_name' => $user->name,
                'cus_email' => $user->email,
                'cus_add1' => $user->address,
                'cus_city' => '',
                'cus_state' => '',
                'cus_postcode' => '',
                'cus_country' => '',
                'cus_phone' => $user->phone,
                'cus_fax' => '',
            ];

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $apiUrl,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_CONNECTTIMEOUT => 30,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postData,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);

            $response = curl_exec($curl);
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if ($code === 200 && $response !== false) {
                $sslResponse = json_decode($response, true);

                if (! empty($sslResponse['GatewayPageURL'])) {
                    Log::info('SSLCommerz payment URL created.', [
                        'gateway_url' => $sslResponse['GatewayPageURL'],
                        'tran_id' => $transactionId,
                    ]);

                    return $sslResponse['GatewayPageURL'];
                }

                Log::warning('SSLCommerz payment creation failed.', ['response' => $sslResponse]);
            }

            return null;
        } catch (Throwable $e) {
            Log::error('SSLCommerz::payment_create() exception', [
                'message' => $e->getMessage(),
                'identifier' => $identifier,
            ]);

            return null;
        }
    }
}
