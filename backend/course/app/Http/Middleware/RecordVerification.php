<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class RecordVerification
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $modelName, string $param1, ?string $param2 = null): Response
    {
        // Convert "Review" → "App\Models\Review"
        $modelClass = 'App\\Models\\'.Str::studly($modelName);

        if (! class_exists($modelClass)) {
            abort(500, "Model {$modelClass} does not exist.");
        }

        // Get route param for param1 (e.g., slug or id)
        $param1Value = $request->route($param1);

        if (! $param1Value) {
            abort(400, "Missing required route parameter: {$param1}.");
        }

        $query = $modelClass::where($param1, $param1Value);

        // If second parameter (e.g. user_id) is provided
        if ($param2) {
            $param2Value = $request->route($param2) ?? Auth::id();
            $query->where($param2, $param2Value);
        }

        // If record doesn't exist, abort
        if (! $query->exists()) {
            abort(404, "Record not found in {$modelName} for given parameters.");
        }

        return $next($request);
    }
}
