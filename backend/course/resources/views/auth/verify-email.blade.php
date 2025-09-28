@extends('layouts.default')

@push('title', get_phrase('Email Verification'))

@section('content')
    <section class="auth-wrapper">
        <div class="d-flex flex-lg-row flex-column align-items-center justify-content-center gap-5">

            <!-- Illustration -->
            <div class="d-flex justify-content-center">
                <img src="{{ asset('assets/frontend/default/image/login.gif') }}" alt="Email Verification Illustration"
                    class="img-fluid auth-illustration">
            </div>

            <!-- Verification Form -->
            <div>
                <div class="auth-card">
                    <form action="{{ route('verification.send') }}" method="POST">
                        @csrf

                        <h3 class="mb-3 text-center">{{ get_phrase('Email Verification 📧') }}</h3>
                        <p class="text-muted mb-4 text-center">
                            {{ get_phrase('Thanks for signing up! Before getting started, please verify your email address by clicking on the link we sent you. If you didn’t receive the email, we can send another.') }}
                        </p>

                        <!-- Success Message -->
                        @if (session('status') == 'verification-link-sent')
                            <div class="alert alert-success text-center" role="alert">
                                {{ get_phrase('A new verification link has been sent to the email address you provided during registration.') }}
                            </div>
                        @endif

                        <!-- Resend Button -->
                        <button type="submit" class="btn btn-primary w-100 btn-auth">
                            {{ get_phrase('Resend Verification Email') }}
                        </button>

                        <!-- Back to Login -->
                        <p class="text-center mt-4 mb-0">
                            <a href="{{ route('login') }}">{{ get_phrase('Back to login page') }}</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
