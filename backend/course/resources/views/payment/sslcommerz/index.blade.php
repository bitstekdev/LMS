@php
    $model = $payment_details['success_method']['model_name'] ?? null;
    $key = '';
    $msg = null;

    if ($model === 'InstructorPayment') {
        $instructorId = $payment_details['items'][1]['id'] ?? null;
        $instructor = \App\Models\User::find($instructorId);
        $sslcommerzKeys = $instructor->paymentkeys['sslcommerz'] ?? [];

        if ($payment_gateway->test_mode == 1) {
            $key = $sslcommerzKeys['store_key'] ?? '';
        } else {
            $key = $sslcommerzKeys['store_live_password'] ?? '';
        }

        if (!$key) {
            $msg = get_phrase('This payment gateway is not configured.');
        }
    } else {
        $sslcommerzKeys = $payment_gateway->keys['sslcommerz'] ?? [];

        if ($payment_gateway->status != 1) {
            $msg = get_phrase('Admin denied transaction through this gateway.');
        } else {
            if ($payment_gateway->test_mode == 1) {
                $key = $sslcommerzKeys['store_password'] ?? '';
            } else {
                $key = $sslcommerzKeys['store_live_password'] ?? '';
            }

            if (!$key) {
                $msg = get_phrase('This payment gateway is not configured.');
            }
        }
    }

    $user = auth()->user();
@endphp

@if ($key)
    <form action="{{ route('payment.create', $payment_gateway->identifier) }}" method="GET">
        @csrf
        <input type="hidden" name="payable_amount" value="{{ $payment_details['payable_amount'] }}">
        <input type="hidden" name="user_id" value="{{ $user->id }}">
        <input type="hidden" name="currency" value="{{ $payment_gateway->currency }}">
        <input type="hidden" name="payment_type" value="{{ $payment_gateway->title }}">
        <input type="hidden" name="items_id" value="{{ $payment_details['items'][0]['id'] ?? '' }}">
        <input type="hidden" name="sslcz_storeid" value="{{ $key }}">

        <button class="btn btn-primary" type="submit">
            {{ get_phrase('Pay by SSLcommerz') }}
        </button>
    </form>
@else
    <div class="alert alert-danger d-flex align-items-center" role="alert">
        <svg xmlns="http://www.w3.org/2000/svg" class="me-2" width="24" height="24" fill="currentColor"
            class="bi bi-exclamation-triangle-fill" viewBox="0 0 16 16">
            <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165
                   13.233c-.457.778.091 1.767.98 1.767h13.713c.889
                   0 1.438-.99.98-1.767L8.982 1.566zM8
                   5c.535 0 .954.462.9.995l-.35
                   3.507a.552.552 0 0 1-1.1
                   0L7.1 5.995A.905.905 0 0 1
                   8 5zm.002 6a1 1 0 1 1
                   0 2 1 1 0 0 1 0-2z" />
        </svg>
        <div class="payment_err_msg">
            <strong>{{ get_phrase('Oops!') }}</strong> {{ $msg }}<br>
            {{ get_phrase('Try another gateway.') }}
        </div>
    </div>
@endif
