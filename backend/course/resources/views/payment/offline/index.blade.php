@php
    $amount = $payment_details['payable_amount'];
    $model = $payment_details['success_method']['model_name'] ?? null;
    $bank_information = '';
    $msg = '';

    if ($model === 'InstructorPayment') {
        $instructor = \App\Models\User::find($payment_details['items'][0]['id']);
        $bank_information = $instructor->paymentkeys['offline']['bank_information'] ?? '';

        if (empty($bank_information)) {
            $msg = get_phrase("This payment gateway isn't configured.");
        }
    } else {
        $keys = $payment_gateway->keys ?? [];

        if ($payment_gateway->status !== 1) {
            $msg = get_phrase('Admin denied transaction through this gateway.');
        } elseif (!empty($keys)) {
            $bank_information = $keys['bank_information'] ?? '';

            if (empty($bank_information)) {
                $msg = get_phrase("This payment gateway isn't configured.");
            }
        } else {
            $msg = get_phrase("This payment gateway isn't configured.");
        }
    }
@endphp

@if (!empty($bank_information))
    <div class="row my-5">
        <div class="col-md-12 text-start">
            {!! removeScripts($bank_information) !!}
        </div>
    </div>

    <form action="{{ route('payment.offline.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label class="form-label d-flex justify-content-between">
                <span>{{ get_phrase('Payment Document') }}</span>
                <span>{{ get_phrase('(jpg, pdf, txt, png, docx)') }}</span>
            </label>

            <input type="hidden" name="item_type" value="{{ $payment_details['custom_field']['item_type'] ?? 'course' }}"
                required>

            <input type="file" name="doc" class="form-control" required>
        </div>

        <input type="submit" class="btn btn-primary" value="{{ get_phrase('Pay offline') }}">
    </form>
@else
    <div class="alert alert-danger d-flex align-items-center mt-4" role="alert">
        <svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:">
            <use xlink:href="#exclamation-triangle-fill" />
        </svg>
        <div class="payment_err_msg">
            <strong>{{ get_phrase('Oops!') }}</strong> {{ $msg }}<br>
            {{ get_phrase('Try another gateway.') }}
        </div>
    </div>
@endif
