<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InstructorBlogPermissionMiddleware
{
    /**
     * Handle an incoming request to check if blog access is allowed.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If blog permission is enabled for instructors
        if ((int) get_frontend_settings('instructors_blog_permission') !== 0) {
            return $next($request);
        }

        // Otherwise, redirect them back to dashboard
        return redirect()->route('instructor.dashboard');
    }
}
