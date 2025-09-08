@php
    use App\Models\PaymentGateway;
    use App\Models\User;

    $identifier = 'paytm';
    $payment_details = session('payment_details');
    $model = $payment_details['success_method']['model_name'] ?? null;
    $user = auth()->user();

    $payment_gateway = PaymentGateway::where('identifier', $identifier)->firstOrFail();

    $keys = [];
    if ($model === 'InstructorPayment') {
        $instructorId = $payment_details['items'][0]['id'] ?? null;
        $instructor = User::find($instructorId);
        $keys = $instructor->paymentkeys['paytm'] ?? [];
    } else {
        $keys = $payment_gateway->keys ?? [];
    }

    // Extract credentials
    $paytm_merchant_key = $keys['paytm_merchant_key'] ?? '';
    $paytm_merchant_mid = $keys['paytm_merchant_mid'] ?? '';
    $paytm_merchant_website = $keys['paytm_merchant_website'] ?? '';
    $industry_type_id = $keys['industry_type_id'] ?? '';
    $channel_id = $keys['channel_id'] ?? '';

    // Environment URLs
    if ($payment_gateway->test_mode == 1) {
        $PAYTM_TXN_URL = 'https://securegw-stage.paytm.in/theia/processTransaction';
        $PAYTM_STATUS_QUERY_URL = 'https://securegw-stage.paytm.in/merchant-status/getTxnStatus';
    } else {
        $PAYTM_TXN_URL = 'https://securegw.paytm.in/theia/processTransaction';
        $PAYTM_STATUS_QUERY_URL = 'https://securegw.paytm.in/merchant-status/getTxnStatus';
    }

    // Define helper functions
    if (!function_exists('generateSalt_e')) {
        function generateSalt_e($length = 4)
        {
            return substr(
                str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', 5)),
                0,
                $length,
            );
        }
    }

    if (!function_exists('checkString_e')) {
        function checkString_e($value)
        {
            return $value === 'null' ? '' : $value;
        }
    }

    if (!function_exists('getArray2Str')) {
        function getArray2Str($array)
        {
            $str = '';
            foreach ($array as $key => $value) {
                $value = checkString_e($value);
                if (strpos($value, 'REFUND') !== false || strpos($value, '|') !== false) {
                    continue;
                }
                $str .= $str === '' ? $value : "|$value";
            }
            return $str;
        }
    }

    if (!function_exists('encrypt_e')) {
        function encrypt_e($input, $key)
        {
            return openssl_encrypt(
                $input,
                'AES-128-CBC',
                html_entity_decode($key),
                0,
                "@@@@&&&&####$$$$",
            );
        }
    }

    if (!function_exists('getChecksumFromArray')) {
        function getChecksumFromArray($array, $key, $sort = 1)
        {
            if ($sort) {
                ksort($array);
            }
            $str = getArray2Str($array);
            $salt = generateSalt_e(4);
            $final = $str . '|' . $salt;
            $hash = hash('sha256', $final) . $salt;
            return encrypt_e($hash, $key);
        }
    }

    // Prepare Paytm params
    $order_id = 'ORDS' . rand(10000, 99999999);
    $customer_id = 'CUST' . ($user->id ?? rand(10000, 999999));
    $amount = $payment_details['payable_amount'] ?? 0;

    $paramList = [
        'MID' => $paytm_merchant_mid,
        'ORDER_ID' => $order_id,
        'CUST_ID' => $customer_id,
        'INDUSTRY_TYPE_ID' => $industry_type_id,
        'CHANNEL_ID' => $channel_id,
        'TXN_AMOUNT' => $amount,
        'WEBSITE' => $paytm_merchant_website,
        'CALLBACK_URL' => $payment_details['success_url'] . '/' . $identifier,
    ];

    $checkSum = getChecksumFromArray($paramList, $paytm_merchant_key);
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Merchant Check Out Page</title>
</head>

<body>
    <center>
        <h1>{{ get_phrase('Please do not refresh this page') }}...</h1>
    </center>

    <form method="post" action="{{ $PAYTM_TXN_URL }}" name="paytm_form">
        @foreach ($paramList as $name => $value)
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
        @endforeach
        <input type="hidden" name="CHECKSUMHASH" value="{{ $checkSum }}">
    </form>

    <script type="text/javascript">
        document.paytm_form.submit();
    </script>
</body>

</html>
