<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\User;
use App\Services\FileUploaderService;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration form.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        // Optional: ReCAPTCHA gate
        if (get_frontend_settings('recaptcha_status') && ! check_recaptcha((string) $request->input('g-recaptcha-response'))) {
            Session::flash('error', get_phrase('Recaptcha verification failed'));

            return redirect()->route('register.form');
        }

        // Validate baseline fields + conditional instructor fields
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'unique:users,email'],
            'password' => ['required', Rules\Password::defaults()],
            'instructor' => ['nullable', 'boolean'],
            'phone' => ['required_if:instructor,1', 'nullable', 'string', 'max:20'],
            'description' => ['required_if:instructor,1', 'nullable', 'string', 'max:2000'],
            'document' => ['required_if:instructor,1', 'nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $user = null;

            DB::transaction(function () use ($request, &$user): void {
                // Create the user
                $userData = [
                    'name' => $request->string('name')->toString(),
                    'email' => $request->string('email')->toString(),
                    'role' => 'student',
                    'status' => 1,
                    'password' => bcrypt($request->string('password')->toString()),
                ];

                // Auto-verify if student email verification is disabled
                if ((int) (get_settings('student_email_verification') ?? 0) !== 1) {
                    $userData['email_verified_at'] = Carbon::now();
                }

                $user = User::create($userData);

                // If applying as an instructor, create an application
                if ($request->boolean('instructor')) {
                    // Block duplicate applications for the same user
                    if (Application::where('user_id', $user->id)->exists()) {
                        throw new RuntimeException('application_exists');
                    }

                    $applicationData = [
                        'user_id' => $user->id,
                        'phone' => (string) $request->input('phone'),
                        'description' => (string) $request->input('description'),
                    ];

                    if ($request->hasFile('document')) {
                        $doc = $request->file('document');
                        $path = 'uploads/applications/'.$user->id.'_'.Str::random(20).'.'.$doc->getClientOriginalExtension();

                        // Resize-or-store via your helper
                        app(FileUploaderService::class)->upload($doc, $path, null, null, 300);
                        $applicationData['document'] = $path;
                    }

                    Application::create($applicationData);
                }
            });

            // Fire registration event + login
            event(new Registered($user));
            Auth::login($user);

            if ($request->boolean('instructor')) {
                Session::flash('success', get_phrase('Your application has been submitted.'));
            }

            // Redirect to intended URL or dashboard as a fallback
            return redirect()->intended(route('dashboard'));
        } catch (RuntimeException $e) {
            if ($e->getMessage() === 'application_exists') {
                Session::flash('error', get_phrase('Your request is in process. Please wait for admin to respond.'));

                return redirect()->route('become.instructor');
            }

            Log::error('Registration runtime error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return redirect()
                ->route('register.form')
                ->withErrors(['general' => get_phrase('Unable to register at this time. Please try again.')])
                ->withInput();
        } catch (Throwable $e) {
            Log::error('Registration failed: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return redirect()
                ->route('register.form')
                ->withErrors(['general' => get_phrase('Something went wrong. Please try again later.')])
                ->withInput();
        }
    }
}
