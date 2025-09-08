<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules;
use Throwable;

class AuthController extends Controller
{
    /**
     * @unauthenticated
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);

            $user = User::where('email', $request->email)->where('status', 1)->first();

            if (! $user || ! Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => $user ? 'Invalid credentials!' : 'User not found!',
                    'data' => null,
                    'errors' => null,
                ], 401);
            }

            if ($user->role !== 'student') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only students are allowed to log in through this API.',
                    'data' => null,
                    'errors' => null,
                ], 400);
            }

            $token = $user->createToken('auth-token')->plainTextToken;
            $user->photo = get_photo('user_image', $user->photo);

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                ],
                'errors' => null,
            ], 201);
        } catch (Throwable $e) {
            Log::error('Login error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'data' => null,
                'errors' => null,
            ], 500);
        }
    }

    public function signup(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);

            $user_data = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role' => 'student',
                'status' => 1,
                'password' => Hash::make($validated['password']),
            ];

            if (! get_settings('student_email_verification')) {
                $user_data['email_verified_at'] = now();
            }

            $user = DB::transaction(fn () => User::create($user_data));

            if (get_settings('student_email_verification')) {
                $user->sendEmailVerificationNotification();
            }

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => $user,
                'errors' => null,
            ], 201);
        } catch (Throwable $e) {
            Log::error('Signup error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the user.',
                'data' => null,
                'errors' => null,
            ], 500);
        }
    }

    public function forgot_password(Request $request)
    {
        try {
            $request->validate(['email' => ['required', 'email']]);

            $status = Password::sendResetLink($request->only('email'));

            return response()->json([
                'success' => $status === Password::RESET_LINK_SENT,
                'message' => $status === Password::RESET_LINK_SENT
                    ? 'Reset Password Link sent successfully to your email.'
                    : 'Failed to send Reset Password Link. Please check the email and try again.',
                'data' => null,
                'errors' => null,
            ], $status === Password::RESET_LINK_SENT ? 200 : 400);
        } catch (Throwable $e) {
            Log::error('Forgot password error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'data' => null,
                'errors' => null,
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully.',
                'data' => null,
                'errors' => null,
            ], 200);
        } catch (Throwable $e) {
            Log::error('Logout error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to logout.',
                'data' => null,
                'errors' => null,
            ], 500);
        }
    }
}
