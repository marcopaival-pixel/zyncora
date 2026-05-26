<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiDocsBasicAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = config('chatbox.api_docs_basic_auth.user');
        $password = config('chatbox.api_docs_basic_auth.password');

        if (! filled($user) || ! filled($password)) {
            return $next($request);
        }

        if ($request->getUser() !== $user || $request->getPassword() !== $password) {
            return response('Unauthorized.', 401, [
                'WWW-Authenticate' => 'Basic realm="Chatbox API Docs"',
            ]);
        }

        return $next($request);
    }
}
