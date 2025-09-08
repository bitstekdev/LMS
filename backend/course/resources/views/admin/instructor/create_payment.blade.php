@foreach (\App\Models\PaymentGateway::where('status', 1)->get() as $payment_gateway)
    <p class="mt-3 mb-3 fw-bold">{{ $payment_gateway->title }}</p>

    @php
        $gateway_keys = $payment_gateway->keys;
        $instructor_keys = $instructor->paymentkeys ?? null;
    @endphp

    @if (is_array($gateway_keys))
        @foreach ($gateway_keys as $key => $value)
            @php
                $input_val = $instructor_keys[$payment_gateway->identifier][$key] ?? '';
            @endphp

            <div class="row mb-3">
                <label for="{{ $payment_gateway->identifier }}{{ $key }}"
                    class="form-label ol-form-label col-sm-2 col-form-label text-capitalize">
                    {{ get_phrase(str_replace('_', ' ', $key)) }}
                </label>

                <div class="col-sm-8">
                    <input type="text" name="paymentkeys[{{ $payment_gateway->identifier }}][{{ $key }}]"
                        id="{{ $payment_gateway->identifier }}{{ $key }}" class="form-control ol-form-control"
                        value="{{ $input_val }}">
                </div>
            </div>
        @endforeach
    @else
        <div class="alert alert-primary" role="alert">
            {{ get_phrase('No API required') }}
        </div>
    @endif

    <div class="m-3">
        <hr>
    </div>
@endforeach
