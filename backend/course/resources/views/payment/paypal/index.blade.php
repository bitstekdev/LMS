@php
    $keys = $payment_gateway->keys ?? [];

    if ($payment_gateway->test_mode == 1) {
        $client_id = $keys['sandbox_client_id'] ?? '';
        $paypalURL = 'https://api.sandbox.paypal.com/v1/';
    } else {
        $client_id = $keys['production_client_id'] ?? '';
        $paypalURL = 'https://api.paypal.com/v1/';
    }

    $currency = $payment_gateway->currency ?? 'USD';
    $amount = $payment_details['payable_amount'] ?? 0;
    $success_url = $payment_details['success_url'] ?? '';
@endphp

<div id="smart-button-container">
    <div class="text-center">
        <div id="paypal-button-container"></div>
    </div>
</div>

<script
    src="https://www.paypal.com/sdk/js?client-id={{ $client_id }}&enable-funding=venmo,card&currency={{ $currency }}"
    data-sdk-integration-source="button-factory"></script>

<script>
    "use strict";

    function initPayPalButton() {
        paypal.Buttons({
            env: '{{ $payment_gateway->test_mode ? 'sandbox' : 'production' }}',
            style: {
                layout: 'vertical',
                label: 'paypal',
                size: 'large',
                shape: 'rect',
                color: 'blue'
            },
            createOrder: function(data, actions) {
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: '{{ number_format($amount, 2, '.', '') }}',
                            currency_code: '{{ $currency }}'
                        }
                    }]
                });
            },
            onApprove: function(data, actions) {
                return actions.order.capture().then(function(details) {
                    console.log('Payment Approved:', data);
                    const redirectUrl = "{{ $success_url }}/{{ $payment_gateway->identifier }}" +
                        "?payment_id=" + data.orderID + "&payer_id=" + details.payer.payer_id;
                    window.location.href = redirectUrl;
                });
            },
            onError: function(err) {
                console.error('PayPal error:', err);
            }
        }).render('#paypal-button-container');
    }

    $(function() {
        const waitForPayPal = setInterval(() => {
            if (typeof paypal !== 'undefined') {
                initPayPalButton();
                clearInterval(waitForPayPal);
            }
        }, 500);
    });
</script>
