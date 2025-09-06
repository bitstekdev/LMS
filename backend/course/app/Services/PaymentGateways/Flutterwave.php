<?php

namespace App\Services\PaymentGateways;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Throwable;

class Flutterwave
{
    use HasFactory;

    /**
     * Handle Flutterwave payment status response.
     */
    public static function payment_status(string $identifier, array $transaction_keys = []): bool
    {
        try {
            if (! empty($transaction_keys)) {
                // Remove the first (possibly meta or token) key
                array_shift($transaction_keys);

                // Store remaining keys in session
                Session::put('keys', $transaction_keys);

                Log::info('Flutterwave payment keys processed.', [
                    'identifier' => $identifier,
                    'keys' => $transaction_keys,
                ]);

                return true;
            }

            Log::warning('Flutterwave payment_status called with empty transaction keys.', [
                'identifier' => $identifier,
            ]);

            return false;
        } catch (Throwable $e) {
            Log::error('Flutterwave::payment_status() error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'identifier' => $identifier,
                'transaction_keys' => $transaction_keys,
            ]);

            return false;
        }
    }
}
