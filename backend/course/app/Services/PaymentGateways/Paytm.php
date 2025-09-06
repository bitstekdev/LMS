<?php

namespace App\Services\PaymentGateways;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Throwable;

class Paytm
{
    /**
     * Handle Paytm payment response and store transaction keys in session.
     */
    public static function payment_status(string $identifier, array $transaction_keys = []): bool
    {
        try {
            if (! empty($transaction_keys)) {
                // Remove the first token if it's meta
                array_shift($transaction_keys);

                // Store the transaction keys in session
                Session::put('keys', $transaction_keys);

                Log::info('Paytm payment keys processed successfully.', [
                    'identifier' => $identifier,
                    'keys' => $transaction_keys,
                ]);

                return true;
            }

            Log::warning('Paytm::payment_status called with empty or invalid transaction keys.', [
                'identifier' => $identifier,
            ]);

            return false;
        } catch (Throwable $e) {
            Log::error('Paytm::payment_status() exception: '.$e->getMessage(), [
                'identifier' => $identifier,
                'transaction_keys' => $transaction_keys,
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }
}
