@extends('layouts.default')

@push('title', get_phrase('Forgot Password'))

@section('content')
    <section class="auth-wrapper">
        <div class="d-flex flex-lg-row flex-column align-items-center justify-content-center gap-5">

            <!-- Illustration -->
            <div class="d-flex justify-content-center">
                <img src="{{ asset('assets/frontend/default/image/login.gif') }}" alt="Forgot Password Illustration"
                    class="img-fluid auth-illustration">
            </div>

            <!-- Forgot Password Form -->
            <div>
                <div class="auth-card">
                    <form action="{{ route('password.email') }}" method="POST">
                        @csrf

                        <h3 class="mb-3 text-center">{{ get_phrase('Forgot Password ❓') }}</h3>
                        <p class="text-muted mb-4 text-center">
                            {{ get_phrase('Enter your account email address and we’ll send you a reset link.') }}
                        </p>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">{{ get_phrase('Email') }}</label>
                            <input type="email" id="email" name="email" class="form-control"
                                placeholder="{{ get_phrase('Enter Your Email') }}" required>
                            @error('email')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Submit -->
                        <button type="submit" class="btn btn-primary w-100 btn-auth">
                            {{ get_phrase('Send Reset Link') }}
                        </button>

                        <!-- Back to login -->
                        <p class="text-center mt-4 mb-0">
                            <a href="{{ route('login') }}">{{ get_phrase('Back to login page') }}</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
