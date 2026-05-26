<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiDocsEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('chatbox.api_docs_enabled', false)) {
            abort(404);
        }

        return $next($request);
    }
}
