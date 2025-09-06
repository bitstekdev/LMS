<?php

namespace App\Services\PaymentGateways;

use App\Models\PaymentGateway;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;

class Doku
{
    /**
     * Handle Doku payment status webhook.
     *
     * @return bool|string|\Illuminate\Http\JsonResponse
     */
    public static function payment_status(string $identifier, array $bodyData = [], array $headerData = [])
    {
        $paymentGateway = PaymentGateway::where('identifier', $identifier)->firstOrFail();
        $keys = $paymentGateway->keys;

        $testMode = $paymentGateway->test_mode === 1;
        $publicKey = $testMode ? $keys['public_test_key'] : $keys['public_live_key'];
        $secretKey = $testMode ? $keys['secret_test_key'] : $keys['secret_live_key'];

        // No body? Just return 'submitted'
        if (empty($bodyData)) {
            return 'submitted';
        }

        try {
            // Authenticate user via email in webhook
            $user = User::where('email', $bodyData['customer']['email'] ?? null)->first();

            if (! $user) {
                Log::warning('Doku webhook: user not found for email', [
                    'email' => $bodyData['customer']['email'] ?? null,
                ]);

                return false;
            }

            Auth::login($user);

            $paymentDetails = json_decode($user->temp, true);

            // Validate expiration and transaction status
            if (
                isset($paymentDetails['expired_on'], $paymentDetails['success_method']) &&
                $paymentDetails['expired_on'] >= time() &&
                ($bodyData['transaction']['status'] ?? null) === 'SUCCESS'
            ) {
                session(['payment_details' => $paymentDetails]);

                $model = trim($paymentDetails['success_method']['model_name']);
                $function = $paymentDetails['success_method']['function_name'];

                $modelClass = "App\\Models\\$model";

                // Check if model and method are valid before executing
                if (class_exists($modelClass) && method_exists($modelClass, $function)) {
                    $modelClass::$function($identifier);
                } else {
                    Log::error('Doku success callback is invalid', [
                        'model' => $modelClass,
                        'function' => $function,
                    ]);

                    return false;
                }

                // Clean up temp session
                $user->update(['temp' => json_encode([])]);
                session()->forget('payment_details');
                Auth::logout();

                Log::info('Doku payment processed successfully.', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);

                return true;
            }

            Log::warning('Doku payment status failed validation.', [
                'payment_details' => $paymentDetails,
                'bodyData' => $bodyData,
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Doku exception: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Something went wrong!'], 500);
        }
    }

    /**
     * Store temporary payment data in user's "temp" column.
     */
    public static function storeTempData(): void
    {
        // Create 'temp' column in users table if it doesn't exist
        if (! Schema::hasColumn('users', 'temp')) {
            Schema::table('users', function (Blueprint $table) {
                $table->text('temp')->nullable();
            });
        }

        $paymentDetails = session('payment_details', []);
        $paymentDetails['expired_on'] = time() + 300; // 5 minutes

        User::where('id', auth('web')->id())->update([
            'temp' => json_encode($paymentDetails),
        ]);

        session()->forget('payment_details');

        Log::info('Temporary payment data stored for user.', [
            'user_id' => auth('web')->id(),
        ]);
    }
}
