<!-- Paystack JS (included only once) -->
<script src="https://js.paystack.co/v1/inline.js"></script>

<!-- Paystack Payment Button -->
<form class="form paystack-form">
    <hr class="border mb-4">
    <button type="button" class="btn btn-primary py-2 px-3" onclick="payWithPaystack()">
        {{ get_phrase('Pay by Paystack') }}
        <span data-toggle="tooltip" title="Paystack Payment" class="premium-icon">
            <i class="fas fa-chess-queen"></i>
        </span>
    </button>
</form>

@php
    $keys = $payment_gateway->keys ?? [];
    $test_mode = $payment_gateway->test_mode == 1;

    $key = $test_mode ? $keys['public_test_key'] ?? '' : $keys['public_live_key'] ?? '';

    $amount = $payment_details['items'][0]['price'] ?? 0;
    $user = auth()->user();
    $success_url = $payment_details['success_url'] ?? '';
    $cancel_url = $payment_details['cancel_url'] ?? '';
@endphp

<script>
    "use strict";

    function payWithPaystack() {
        var handler = PaystackPop.setup({
            key: '{{ $key }}',
            email: '{{ $user->email }}',
            amount: '{{ number_format($amount * 100, 0, '', '') }}',
            currency: '{{ $payment_gateway->currency }}',
            metadata: {
                custom_fields: [{
                    display_name: '{{ $user->name }}',
                    variable_name: 'paid_on',
                    value: '{{ route('payment.success', $payment_gateway->identifier) }}'
                }]
            },
            callback: function(response) {
                console.log("Paystack success:", response);
                window.location.replace(
                    '{{ $success_url }}/{{ $payment_gateway->identifier }}?reference=' + response
                    .reference
                );
            },
            onClose: function() {
                console.log("Paystack payment closed.");
                window.location.replace('{{ $cancel_url }}');
            }
        });

        handler.openIframe();
    }
</script>
