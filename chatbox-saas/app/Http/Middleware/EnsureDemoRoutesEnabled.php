<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDemoRoutesEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('chatbox.demo_routes_enabled', false)) {
            abort(404);
        }

        return $next($request);
    }
}
