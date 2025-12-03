<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DriverMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth('sanctum')->user();

        if ($user && $user->role === 'driver') {
            return $next($request);
        }

        return response()->json(['message' => 'Unauthorized. Only drivers allowed.'], 403);
    }
}
