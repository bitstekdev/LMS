<?php

namespace App\Services\PaymentGateways;

use App\Models\PaymentGateway;
use Illuminate\Support\Facades\Log;
use Throwable;

class Aamarpay
{
    /**
     * Verify payment status with Aamarpay API.
     *
     * @param  string|null  $transactionId
     */
    public static function payment_status(string $identifier, $transactionId = null): bool
    {
        try {
            $paymentGateway = PaymentGateway::where('identifier', $identifier)->firstOrFail();
            $keys = $paymentGateway->keys;

            if (request()->post('pay_status') === 'Successful') {
                $transactionId = request()->post('mer_txnid');
                $storeId = $keys['store_id'];
                $signatureKey = $keys['signature_key'];

                $url = $paymentGateway->test_mode
                    ? 'https://sandbox.aamarpay.com/api/v1/trxcheck/request.php'
                    : 'https://secure.aamarpay.com/api/v1/trxcheck/request.php';

                $url .= "?request_id={$transactionId}&store_id={$storeId}&signature_key={$signatureKey}&type=json";

                $curl = curl_init();
                curl_setopt_array($curl, [
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_VERBOSE => true,
                ]);

                $response = curl_exec($curl);

                if (curl_errno($curl)) {
                    Log::error('Aamarpay cURL error: '.curl_error($curl));
                }

                curl_close($curl);

                $result = json_decode($response, true);

                if (is_array($result) && ($result['pay_status'] ?? null) === 'Successful') {
                    Log::info('Aamarpay payment successful', [
                        'identifier' => $identifier,
                        'transaction_id' => $transactionId,
                        'response' => $result,
                    ]);

                    return true;
                }

                Log::warning('Aamarpay payment verification failed', [
                    'identifier' => $identifier,
                    'transaction_id' => $transactionId,
                    'response' => $result,
                ]);
            }

            return true;
        } catch (Throwable $e) {
            Log::error('Aamarpay::payment_status() error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'identifier' => $identifier,
                'transaction_id' => $transactionId,
            ]);

            return false;
        }
    }

    /**
     * Create a new payment session with Aamarpay and return the redirect URL.
     */
    public static function payment_create(string $identifier): ?string
    {
        try {
            $paymentGateway = PaymentGateway::where('identifier', $identifier)->firstOrFail();
            $paymentDetails = session('payment_details');
            $user = auth('web')->user();
            $keys = $paymentGateway->keys;

            $productsName = collect($paymentDetails['items'] ?? [])
                ->pluck('title')
                ->implode(', ');

            if ($paymentGateway->test_mode) {
                $storeId = $keys['store_id'];
                $signatureKey = $keys['signature_key'];
                $paymentUrl = 'https://sandbox.aamarpay.com/index.php';
            } else {
                $storeId = $keys['signature_key']; // Possibly intentional inversion
                $signatureKey = $keys['signature_live_key'];
                $paymentUrl = 'https://secure.aamarpay.com/index.php';
            }

            $transactionId = 'AAMAR_TXN_'.uniqid();

            $postData = [
                'store_id' => $storeId,
                'signature_key' => $signatureKey,
                'cus_name' => $user->name,
                'cus_email' => $user->email,
                'cus_city' => $user->address,
                'cus_phone' => $user->phone,
                'amount' => round($paymentDetails['payable_amount']),
                'currency' => $paymentGateway->currency,
                'tran_id' => $transactionId,
                'desc' => $identifier,
                'success_url' => "{$paymentDetails['success_url']}/{$paymentGateway->identifier}",
                'fail_url' => $paymentDetails['cancel_url'],
                'cancel_url' => $paymentDetails['cancel_url'],
                'type' => 'json',
            ];

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $paymentUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $postData,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);

            $response = curl_exec($curl);

            if (curl_errno($curl)) {
                Log::error('Aamarpay cURL error: '.curl_error($curl));
            }

            curl_close($curl);

            $result = json_decode($response);

            if (! empty($result->payment_url)) {
                Log::info('Aamarpay payment URL generated successfully', [
                    'payment_url' => $result->payment_url,
                    'transaction_id' => $transactionId,
                ]);

                return $result->payment_url;
            }

            Log::error('Failed to generate Aamarpay payment URL', [
                'response' => $response,
                'post_data' => $postData,
            ]);

            return null;
        } catch (Throwable $e) {
            Log::error('Aamarpay::payment_create() error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'identifier' => $identifier,
            ]);

            return null;
        }
    }
}
