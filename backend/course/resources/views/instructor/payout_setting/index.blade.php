@extends('layouts.instructor')

@push('title', get_phrase('Payout setting'))
@push('meta') @endpush
@push('css') @endpush

@section('content')
    @php
        $user_data = \App\Models\User::find(auth()->id());
        $payment_keys = $user_data->paymentkeys ?? [];
        $payment_gateways = \App\Models\PaymentGateway::where('identifier', '!=', 'offline')->get();
    @endphp

    <div class="ol-card radius-8px">
        <div class="ol-card-body my-3 py-4 px-20px">
            <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap flex-md-nowrap">
                <h4 class="title fs-16px">
                    <i class="fi-rr-settings-sliders me-2"></i>
                    {{ get_phrase('Payout setting') }}
                </h4>
            </div>
        </div>
    </div>

    <div class="alert alert-primary ol-alert-primary mb-3" role="alert">
        <p class="sub-title2 fs-16px">
            <span class="title2">{{ get_phrase('Be careful !!') }}</span>
            {{ get_phrase('Just configure the payment gateway you want to use, leave the rest blank.') }}
            {{ get_phrase('Also, make sure that you have configured your payment settings correctly') }}
        </p>
    </div>

    <form action="{{ route('instructor.payout.setting.store') }}" method="POST" class="mb-5"
        enctype="multipart/form-data">
        @csrf
        <div class="row g-3">
            @foreach ($payment_gateways as $payment_gateway)
                @php
                    $keys = $payment_gateway->keys ?? [];
                    $user_keys = $payment_keys[$payment_gateway->identifier] ?? [];
                @endphp

                <div class="col-md-6">
                    <div class="ol-card p-3">
                        <div class="ol-card-body {{ $payment_gateway->status != 1 ? 'd-none' : '' }}">
                            <h4 class="title fs-16px mb-3">{{ $payment_gateway->title }}</h4>

                            @foreach ($keys as $index => $label)
                                @php
                                    $input_id = $payment_gateway->identifier . $index;
                                    $input_value = $user_keys[$index] ?? '';
                                    $placeholder = get_phrase(ucfirst(str_replace('_', ' ', $index)));
                                @endphp

                                <div class="mb-3">
                                    <input type="text" id="{{ $input_id }}"
                                        name="gateways[{{ $payment_gateway->identifier }}][{{ $index }}]"
                                        value="{{ old("gateways.{$payment_gateway->identifier}.{$index}", $input_value) }}"
                                        class="form-control ol-form-control" placeholder="{{ $placeholder }}">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row">
            <div class="col-4 offset-4">
                <button class="btn ol-btn-primary mt-4 w-100" type="submit">
                    {{ get_phrase('Save changes') }}
                </button>
            </div>
        </div>
    </form>
@endsection

@push('js') @endpush
