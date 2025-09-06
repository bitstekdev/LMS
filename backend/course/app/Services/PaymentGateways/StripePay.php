<?php

namespace App\Services\PaymentGateways;

use App\Models\PaymentGateway;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Stripe\Checkout\Session as CheckoutSession;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Throwable;

class StripePay extends Model
{
    public static function payment_status(string $identifier, array $transaction_keys = []): bool
    {
        try {
            $paymentGateway = PaymentGateway::where('identifier', $identifier)->firstOrFail();
            $keys = $paymentGateway->keys;
            $sessionId = $transaction_keys['session_id'] ?? null;

            if (! $sessionId) {
                Log::warning('Stripe payment_status: Missing session_id', [
                    'identifier' => $identifier,
                    'transaction_keys' => $transaction_keys,
                ]);

                return false;
            }

            $secretKey = $paymentGateway->test_mode
                ? $keys['secret_key']
                : $keys['secret_live_key'];

            Stripe::setApiKey($secretKey);

            // Retrieve Checkout Session
            $checkoutSession = CheckoutSession::retrieve($sessionId);
            $paymentIntent = PaymentIntent::retrieve($checkoutSession->payment_intent);

            if ($paymentIntent->status === 'succeeded') {
                Session::put('session_id', $sessionId);

                Log::info('Stripe payment succeeded', [
                    'identifier' => $identifier,
                    'session_id' => $sessionId,
                ]);

                return true;
            }

            Log::warning('Stripe payment not succeeded', [
                'identifier' => $identifier,
                'session_id' => $sessionId,
                'status' => $paymentIntent->status,
            ]);

            return false;

        } catch (ApiErrorException $e) {
            Log::error('Stripe API error during payment_status', [
                'error' => $e->getMessage(),
                'identifier' => $identifier,
            ]);

            return false;

        } catch (Throwable $e) {
            Log::error('Stripe general error during payment_status', [
                'error' => $e->getMessage(),
                'identifier' => $identifier,
            ]);

            return false;
        }
    }

    public static function payment_create(string $identifier): ?string
    {
        try {
            $paymentGateway = PaymentGateway::where('identifier', $identifier)->firstOrFail();
            $keys = $paymentGateway->keys;
            $paymentDetails = session('payment_details');

            $secretKey = $paymentGateway->test_mode
                ? $keys['secret_key']
                : $keys['secret_live_key'];

            Stripe::setApiKey($secretKey);

            $productsName = collect($paymentDetails['items'] ?? [])
                ->pluck('title')
                ->implode(', ');

            $checkoutSession = CheckoutSession::create([
                'line_items' => [
                    [
                        'price_data' => [
                            'product_data' => [
                                'name' => 'Purchasing '.$productsName,
                            ],
                            'unit_amount' => round($paymentDetails['payable_amount'] * 100, 2),
                            'currency' => $paymentGateway->currency,
                        ],
                        'quantity' => 1,
                    ],
                ],
                'mode' => 'payment',
                'success_url' => $paymentDetails['success_url'].'/'.$identifier.'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $paymentDetails['cancel_url'],
            ]);

            Log::info('Stripe checkout session created successfully', [
                'identifier' => $identifier,
                'checkout_url' => $checkoutSession->url,
            ]);

            return $checkoutSession->url;

        } catch (ApiErrorException $e) {
            Log::error('Stripe API error during payment_create', [
                'error' => $e->getMessage(),
                'identifier' => $identifier,
            ]);

            return null;

        } catch (Throwable $e) {
            Log::error('Stripe general error during payment_create', [
                'error' => $e->getMessage(),
                'identifier' => $identifier,
            ]);

            return null;
        }
    }
}
