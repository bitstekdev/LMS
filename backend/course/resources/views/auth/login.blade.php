@extends('layouts.default')

@push('title', get_phrase('Log In'))

@section('content')
    <section class="auth-wrapper">
        <div class="d-flex flex-lg-row flex-column align-items-center justify-content-center gap-5">

            <!-- Illustration -->
            <div class="col-lg-6 d-none d-lg-flex justify-content-center">
                <img src="{{ asset('assets/frontend/default/image/login.gif') }}" alt="Login Illustration"
                    class="img-fluid auth-illustration">
            </div>

            <!-- Login Form -->
            <div class="col-lg-5 col-md-8 col-sm-10">
                <div class="auth-card">
                    <form action="{{ route('login') }}" method="POST" id="login-form">
                        @csrf

                        <h3 class="mb-3 text-center">{{ get_phrase('Welcome Back 👋') }}</h3>
                        <p class="text-muted mb-4 text-center">
                            {{ get_phrase('Log in to continue your journey with us') }}
                        </p>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">{{ get_phrase('Email') }}</label>
                            <input type="email" name="email" id="email" class="form-control"
                                placeholder="{{ get_phrase('Your Email') }}" required>
                        </div>

                        <!-- Password -->
                        <div class="mb-3 position-relative">
                            <label for="password" class="form-label">{{ get_phrase('Password') }}</label>
                            <input type="password" name="password" id="password" class="form-control"
                                placeholder="********" required>
                            <span id="togglePassword" class="password-toggle">
                                <i class="bi bi-eye-slash"></i>
                            </span>
                        </div>

                        <!-- Remember + Forgot -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label for="remember" class="form-check-label">{{ get_phrase('Remember Me') }}</label>
                            </div>
                            <a href="{{ route('password.request') }}" class="small">
                                {{ get_phrase('Forgot Password?') }}
                            </a>
                        </div>

                        <!-- Submit -->
                        @if (get_frontend_settings('recaptcha_status'))
                            <button class="btn btn-primary w-100 btn-auth g-recaptcha"
                                data-sitekey="{{ get_frontend_settings('recaptcha_sitekey') }}"
                                data-callback='onLoginSubmit' data-action='submit'>
                                {{ get_phrase('Login') }}
                            </button>
                        @else
                            <button type="submit" class="btn btn-primary w-100 btn-auth">
                                {{ get_phrase('Login') }}
                            </button>
                        @endif

                        <!-- Register -->
                        <p class="text-center mt-4 mb-0">
                            {{ get_phrase("Don't have an account?") }}
                            <a href="{{ route('register.form') }}">{{ get_phrase('Create Account') }}</a>
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

        // Toggle password visibility
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

        // Recaptcha callback
        function onLoginSubmit(token) {
            document.getElementById("login-form").submit();
        }
    </script>
@endpush
