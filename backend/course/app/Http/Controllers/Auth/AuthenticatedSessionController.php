<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\DeviceIp;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(Request $request): View|RedirectResponse
    {
        if ($request->user_agent) {
            $session = DeviceIp::where('user_agent', $request->user_agent)->first();
            if ($session) {
                $sessionFilePath = storage_path('framework/sessions/'.$session->session_id);
                if (File::exists($sessionFilePath)) {
                    File::delete($sessionFilePath);
                }

                $session->delete();

                Session::flash('success', get_phrase('You have successfully verified. You can login now.'));
            }

            return redirect()->route('login');
        }

        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request): RedirectResponse
    {
        // Step 1: Validate input
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Step 2: Optional reCAPTCHA check
        if (
            get_frontend_settings('recaptcha_status') &&
            ! check_recaptcha($request->input('g-recaptcha-response'))
        ) {
            Session::flash('error', get_phrase('Recaptcha verification failed'));

            return redirect()->route('login');
        }

        // Step 3: Throttle protection
        $throttleKey = Str::lower($request->input('email')).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            event(new Lockout($request));
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => __('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ]),
            ]);
        }

        // Step 4: Attempt login
        if (! Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey);

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        // Step 5: Device limit enforcement (non-admins only)
        if (Auth::check() && auth('web')->user()->role !== 'admin') {
            $user = Auth::user();
            $sessionId = $request->session()->getId();
            $ipAddress = $request->ip();
            $userAgent = base64_encode($user->id.$request->header('User-Agent'));
            $allowedDevices = get_settings('device_limitation') ?? 1;

            $existingDevices = DeviceIp::where('user_id', $user->id)->get();

            if ($existingDevices->where('user_agent', '!=', $userAgent)->count() < $allowedDevices) {
                DeviceIp::updateOrInsert(
                    ['user_id' => $user->id, 'user_agent' => $userAgent],
                    ['ip_address' => $ipAddress, 'session_id' => $sessionId, 'updated_at' => now()]
                );
            } else {
                // Exceeded device limit — send verification email
                $oldestDevice = $existingDevices->sortBy('id')->first();
                $verificationLink = route('login', ['user_agent' => $oldestDevice->user_agent]);

                try {
                    Mail::send('email.new_device_login_verification', [
                        'verification_link' => $verificationLink,
                    ], function ($message) use ($user) {
                        $message->to($user->email, $user->name)
                            ->subject('New Device Login Confirmation');
                    });

                    $this->logoutSession($request);
                    Session::flash('success', get_phrase('A confirmation email has been sent. Please check your inbox.'));

                    return redirect()->route('login');
                } catch (\Exception $e) {
                    $this->logoutSession($request);
                    Session::flash('error', get_phrase('Failed to send confirmation email.'));

                    return redirect()->route('login');
                }
            }
        }

        // Step 6: Redirect to intended route
        return redirect()->intended(route('dashboard'));
    }

    /**
     * Logout the user and destroy the session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = auth('web')->user();

        if ($user) {
            $userAgent = base64_encode($user->id.$request->header('User-Agent'));
            DeviceIp::where('user_id', $user->id)->where('user_agent', $userAgent)->delete();
        }

        $this->logoutSession($request);

        return redirect()->route('login');
    }

    /**
     * Logout helper
     */
    private function logoutSession(Request $request): void
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
