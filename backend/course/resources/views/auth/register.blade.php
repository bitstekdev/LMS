@extends('layouts.default')

@push('title', get_phrase('Sign Up'))

@section('content')
    <section class="auth-wrapper">
        <div class="d-flex flex-lg-row flex-column align-items-center justify-content-center gap-5">

            <!-- Illustration -->
            <div class="d-flex justify-content-center">
                <img src="{{ asset('assets/frontend/default/image/signup.gif') }}" alt="Register Illustration"
                    class="img-fluid auth-illustration">
            </div>

            <!-- Sign Up Form -->
            <div>
                <div class="auth-card">
                    <form action="{{ route('register') }}" method="post" enctype="multipart/form-data" id="register-form">
                        @csrf

                        <h3 class="mb-3 text-center">{{ get_phrase('Create an Account ✨') }}</h3>
                        <p class="text-muted mb-4 text-center">
                            {{ get_phrase('Join us and start your journey!') }}
                        </p>

                        <!-- Name -->
                        <div class="mb-3">
                            <label class="form-label">{{ get_phrase('Name') }}</label>
                            <input type="text" name="name" class="form-control" placeholder="Your Name" required>
                            @error('name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label class="form-label">{{ get_phrase('Email') }}</label>
                            <input type="email" name="email" class="form-control" placeholder="Your Email" required>
                            @error('email')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="mb-3 position-relative">
                            <label class="form-label">{{ get_phrase('Password') }}</label>
                            <input type="password" name="password" id="password" class="form-control"
                                placeholder="********" required>
                            <span id="togglePassword" class="password-toggle"><i class="bi bi-eye-slash"></i></span>
                            @error('password')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        @if (get_settings('allow_instructor'))
                            <!-- Instructor Checkbox -->
                            <div class="mb-3 form-check">
                                <input id="instructor" type="checkbox" name="instructor" class="form-check-input">
                                <label for="instructor" class="form-check-label">
                                    {{ get_phrase('Apply to Become an Instructor') }}
                                </label>
                            </div>

                            <!-- Extra Fields -->
                            <div id="become-instructor-fields" class="d-none">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">{{ get_phrase('Phone') }}</label>
                                    <input class="form-control" id="phone" type="tel" name="phone"
                                        placeholder="{{ get_phrase('Enter your phone number') }}">
                                </div>
                                <div class="mb-3">
                                    <label for="document" class="form-label">{{ get_phrase('Document') }}
                                        <small>(doc, docs, pdf, txt, png, jpg, jpeg)</small>
                                    </label>
                                    <input class="form-control" id="document" type="file" name="document">
                                    <small>{{ get_phrase('Provide some documents about your qualifications') }}</small>
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">{{ get_phrase('Message') }}</label>
                                    <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                                </div>
                            </div>
                        @endif

                        <!-- Submit -->
                        @if (get_frontend_settings('recaptcha_status'))
                            <button class="btn btn-primary w-100 btn-auth g-recaptcha"
                                data-sitekey="{{ get_frontend_settings('recaptcha_sitekey') }}"
                                data-callback='onRegisterSubmit' data-action='submit'>
                                {{ get_phrase('Sign Up') }}
                            </button>
                        @else
                            <button type="submit" class="btn btn-primary w-100 btn-auth">
                                {{ get_phrase('Sign Up') }}
                            </button>
                        @endif

                        <!-- Already Have Account -->
                        <p class="text-center mt-4 mb-0">
                            {{ get_phrase('Already have an account?') }}
                            <a href="{{ route('login') }}">{{ get_phrase('Sign in') }}</a>
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

        // Instructor toggle
        document.getElementById('instructor')?.addEventListener('change', function() {
            let fields = document.getElementById('become-instructor-fields');
            if (this.checked) {
                fields.classList.remove('d-none');
            } else {
                fields.classList.add('d-none');
            }
        });

        // Recaptcha callback
        function onRegisterSubmit(token) {
            document.getElementById("register-form").submit();
        }
    </script>
@endpush
