@extends('layouts.default')

@push('title', get_phrase('Reset Password'))

@section('content')
    <section class="auth-wrapper">
        <div class="d-flex flex-lg-row flex-column align-items-center justify-content-center gap-5">

            <!-- Illustration -->
            <div class="d-flex justify-content-center">
                <img src="{{ asset('assets/frontend/default/image/login.gif') }}" alt="Reset Password Illustration"
                    class="img-fluid auth-illustration">
            </div>

            <!-- Reset Password Form -->
            <div>
                <div class="auth-card">
                    <form action="{{ route('password.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="token" value="{{ $request->route('token') }}">

                        <h3 class="mb-3 text-center">{{ get_phrase('Reset Password 🔑') }}</h3>
                        <p class="text-muted mb-4 text-center">
                            {{ get_phrase('Enter your email and choose a new password') }}
                        </p>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">{{ get_phrase('Email') }}</label>
                            <input type="email" id="email" name="email" value="{{ old('email', $request->email) }}"
                                class="form-control" placeholder="{{ get_phrase('Your Email') }}" required>
                            @error('email')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="mb-3 position-relative">
                            <label for="password" class="form-label">{{ get_phrase('Password') }}</label>
                            <input type="password" id="password" name="password" class="form-control"
                                placeholder="{{ get_phrase('New Password') }}" required>
                            <span id="togglePassword" class="password-toggle"><i class="bi bi-eye-slash"></i></span>
                            @error('password')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-3 position-relative">
                            <label for="password_confirmation"
                                class="form-label">{{ get_phrase('Confirm Password') }}</label>
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                class="form-control" placeholder="{{ get_phrase('Confirm Password') }}" required>
                            <span id="togglePasswordConfirm" class="password-toggle"><i class="bi bi-eye-slash"></i></span>
                        </div>

                        <!-- Submit -->
                        <button type="submit" class="btn btn-primary w-100 btn-auth mt-2">
                            {{ get_phrase('Reset Password') }}
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

@push('js')
    <script>
        "use strict";

        // Password toggle
        document.getElementById('togglePassword').addEventListener('click', function() {
            let password = document.getElementById('password');
            let icon = this.querySelector('i');
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            } else {
                password.type = 'password';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            }
        });

        // Confirm password toggle
        document.getElementById('togglePasswordConfirm').addEventListener('click', function() {
            let password = document.getElementById('password_confirmation');
            let icon = this.querySelector('i');
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            } else {
                password.type = 'password';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            }
        });
    </script>
@endpush
