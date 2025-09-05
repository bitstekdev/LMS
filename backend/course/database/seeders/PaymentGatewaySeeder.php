<?php

namespace Database\Seeders;

use App\Models\PaymentGateway;
use Illuminate\Database\Seeder;

class PaymentGatewaySeeder extends Seeder
{
    public function run(): void
    {
        $gateways = [
            [
                'identifier' => 'paypal',
                'currency' => 'USD',
                'title' => 'Paypal',
                'model_name' => 'Paypal',
                'description' => '',
                'keys' => [
                    'sandbox_client_id' => 'AfGaziKslex-scLAyYdDYXNFaz2aL5qGau-SbDgE_D2E80D3AFauLagP8e0kCq9au7W4IasmFbirUUYc',
                    'sandbox_secret_key' => 'EMa5pCTuOpmHkhHaCGibGhVUcKg0yt5-C3CzJw-OWJCzaXXzTlyD17SICob_BkfM_0Nlk7TWnN42cbGz',
                    'production_client_id' => '1234',
                    'production_secret_key' => '12345',
                ],
                'status' => 1,
                'test_mode' => 1,
                'is_addon' => 0,
            ],
            [
                'identifier' => 'stripe',
                'currency' => 'USD',
                'title' => 'Stripe',
                'model_name' => 'StripePay',
                'description' => '',
                'keys' => [
                    'public_key' => 'pk_test_c6VvBEbwHFdulFZ62q1IQrar',
                    'secret_key' => 'sk_test_9IMkiM6Ykxr1LCe2dJ3PgaxS',
                    'public_live_key' => 'pk_live_xxxxxxxxxxxxxxxxxxxxxxxx',
                    'secret_live_key' => 'sk_live_xxxxxxxxxxxxxxxxxxxxxxxx',
                ],
                'status' => 1,
                'test_mode' => 1,
                'is_addon' => 0,
            ],
            [
                'identifier' => 'razorpay',
                'currency' => 'INR',
                'title' => 'Razorpay',
                'model_name' => 'Razorpay',
                'description' => '',
                'keys' => [
                    'public_key' => 'rzp_test_J60bqBOi1z1aF5',
                    'secret_key' => 'uk935K7p4j96UCJgHK8kAU4q',
                ],
                'status' => 1,
                'test_mode' => 1,
                'is_addon' => 0,
            ],
            [
                'identifier' => 'flutterwave',
                'currency' => 'USD',
                'title' => 'Flutterwave',
                'model_name' => 'Flutterwave',
                'description' => '',
                'keys' => [
                    'public_key' => 'FLWPUBK_TEST-48dfbeb50344ecd8bc075b4ffe9ba266-X',
                    'secret_key' => 'FLWSECK_TEST-1691582e23bd6ee4fb04213ec0b862dd-X',
                ],
                'status' => 1,
                'test_mode' => 1,
                'is_addon' => 0,
            ],
            [
                'identifier' => 'paytm',
                'currency' => 'INR',
                'title' => 'Paytm',
                'model_name' => 'Paytm',
                'description' => '',
                'keys' => [
                    'paytm_merchant_key' => 'NLcIjJn!!lkjDZQN',
                    'paytm_merchant_mid' => 'YEPkQv98980476147162',
                    'paytm_merchant_website' => 'WEBSTAGING',
                    'industry_type_id' => 'Retail',
                    'channel_id' => 'WEB',
                ],
                'status' => 1,
                'test_mode' => 1,
                'is_addon' => 0,
            ],
            [
                'identifier' => 'offline',
                'currency' => 'USD',
                'title' => 'Offline Payment',
                'model_name' => 'OfflinePayment',
                'description' => '',
                'keys' => [
                    'bank_information' => 'Write your bank information and instructions here',
                ],
                'status' => 1,
                'test_mode' => 0,
                'is_addon' => 0,
            ],
            [
                'identifier' => 'paystack',
                'currency' => 'NGN',
                'title' => 'Paystack',
                'model_name' => 'Paystack',
                'description' => null,
                'keys' => [
                    'secret_test_key' => 'sk_test_c746060e693dd50c6f397dffc6c3b2f655217c94',
                    'public_test_key' => 'pk_test_0816abbed3c339b8473ff22f970c7da1c78cbe1b',
                    'secret_live_key' => 'sk_live_xxxxxxxxxxxxxxxxxxxxxxxxx',
                    'public_live_key' => 'pk_live_xxxxxxxxxxxxxxxxxxxxxxxxx',
                ],
                'status' => 1,
                'test_mode' => 1,
                'is_addon' => 0,
            ],
            [
                'identifier' => 'sslcommerz',
                'currency' => 'BDT',
                'title' => 'SSLCommerz',
                'model_name' => 'Sslcommerz',
                'description' => null,
                'keys' => [
                    'store_key' => 'creatxxxxxxxxxxx',
                    'store_password' => 'creatxxxxxxxx@ssl',
                    'store_live_key' => 'st_live_xxxxxxxxxxxxxxxxxxxxxxxx',
                    'store_live_password' => 'sp_live_xxxxxxxxxxxxxxxxxxxxxxxx',
                    'sslcz_testmode' => 'true',
                    'is_localhost' => 'true',
                    'sslcz_live_testmode' => 'false',
                    'is_live_localhost' => 'false',
                ],
                'status' => 1,
                'test_mode' => 1,
                'is_addon' => 0,
            ],
            [
                'identifier' => 'aamarpay',
                'currency' => 'BDT',
                'title' => 'Aamarpay',
                'model_name' => 'Aamarpay',
                'description' => null,
                'keys' => [
                    'store_id' => 'xxxxxxxxxxxxx',
                    'signature_key' => 'xxxxxxxxxxxxxxxxxxx',
                    'store_live_id' => 'st_live_xxxxxxxxxxxxxxxxxxxxxxxx',
                    'signature_live_key' => 'si_live_xxxxxxxxxxxxxxxxxxxxxxxx',
                ],
                'status' => 1,
                'test_mode' => 1,
                'is_addon' => 0,
            ],
            [
                'identifier' => 'doku',
                'currency' => 'IDR',
                'title' => 'Doku',
                'model_name' => 'Doku',
                'description' => null,
                'keys' => [
                    'client_id' => 'BRN-xxxx-xxxxxxxxxxxxx',
                    'secret_test_key' => 'SK-xxxxxxxxxxxxxxxxxxxx',
                    'public_test_key' => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
                ],
                'status' => 1,
                'test_mode' => 1,
                'is_addon' => 0,
            ],
            [
                'identifier' => 'maxicash',
                'currency' => 'USD',
                'title' => 'Maxicash',
                'model_name' => 'Maxicash',
                'description' => null,
                'keys' => [
                    'merchant_id' => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
                    'merchant_password' => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
                    'merchant_live_id' => 'mr_live_xxxxxxxxxxxxxxxxxxxxxxxx',
                    'merchant_live_password' => 'mp_live_xxxxxxxxxxxxxxxxxxxxxxxx',
                ],
                'status' => 1,
                'test_mode' => 1,
                'is_addon' => 0,
            ],
        ];

        foreach ($gateways as $gateway) {
            PaymentGateway::updateOrCreate(
                ['identifier' => $gateway['identifier']],
                $gateway
            );
        }
    }
}
