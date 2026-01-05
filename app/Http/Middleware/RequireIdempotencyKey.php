<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireIdempotencyKey
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->header('Idempotency-Key')) {
            return response()->json([
                'error' => 'Idempotency-Key header is required.'
            ], 422);
        }

        return $next($request);
    }
}
