<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class InstructorMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $user->role === 'instructor') {
            return $next($request);
        }

        // Optional: You can flash a message if needed
        // session()->flash('error', 'You must be logged in as an instructor.');

        return redirect()->route('login');
    }
}
