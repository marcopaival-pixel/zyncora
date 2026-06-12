<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogPerformanceMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        $endTime = microtime(true);
        $durationMs = round(($endTime - $startTime) * 1000);

        // Se a requisição demorar mais de 500ms, registramos no log
        if ($durationMs > 500) {
            Log::channel('daily')->warning('Slow Request Detected', [
                'duration_ms' => $durationMs,
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_id' => $request->user()?->id,
            ]);
        }

        return $response;
    }
}
