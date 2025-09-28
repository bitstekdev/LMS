@extends('layouts.default')

@push('title', get_phrase('Confirm Password'))

@section('content')
    <section class="auth-wrapper">
        <div class="d-flex flex-lg-row flex-column align-items-center justify-content-center gap-5">

            <!-- Illustration -->
            <div class="d-flex justify-content-center">
                <img src="{{ asset('assets/frontend/default/image/login.gif') }}" alt="Confirm Password Illustration"
                    class="img-fluid auth-illustration">
            </div>

            <!-- Confirm Password Form -->
            <div>
                <div class="auth-card">
                    <form action="{{ route('password.confirm') }}" method="POST">
                        @csrf

                        <h3 class="mb-3 text-center">{{ get_phrase('Confirm Password 🔒') }}</h3>
                        <p class="text-muted mb-4 text-center">
                            {{ get_phrase('This is a secure area of the application. Please confirm your password before continuing.') }}
                        </p>

                        <!-- Password -->
                        <div class="mb-3 position-relative">
                            <label for="password" class="form-label">{{ get_phrase('Password') }}</label>
                            <input type="password" id="password" name="password" class="form-control"
                                placeholder="{{ get_phrase('Enter Your Password') }}" required>
                            <span id="togglePassword" class="password-toggle">
                                <i class="bi bi-eye-slash"></i>
                            </span>
                            @error('password')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Submit -->
                        <button type="submit" class="btn btn-primary w-100 btn-auth">
                            {{ get_phrase('Confirm') }}
                        </button>
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
    </script>
@endpush
