<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

class AuthRateLimiter
{
    public function handle(Request $request, Closure $next)
    {
        $key = 'auth:' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many login attempts',
                'data' => null,
                'errors' => [
                    'rate_limit' => ['Please wait before trying again.'],
                ],
            ], 429);
        }

        RateLimiter::hit($key, 60);
        
        return $next($request);
    }
}