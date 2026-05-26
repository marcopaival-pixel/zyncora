<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHealthCheckToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = config('chatbox.monitoring.health_check_token');

        if (! filled($token)) {
            abort(404);
        }

        $provided = $request->bearerToken() ?? $request->query('token');

        if (! hash_equals((string) $token, (string) $provided)) {
            abort(401);
        }

        return $next($request);
    }
}
