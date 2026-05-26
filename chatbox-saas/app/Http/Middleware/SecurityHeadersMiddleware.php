<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Content-Security-Policy', $this->buildContentSecurityPolicy());

        return $response;
    }

    protected function buildContentSecurityPolicy(): string
    {
        $csp = config('security.csp', []);

        $scriptSrc = $csp['script_src'] ?? ["'self'"];
        if ($csp['allow_unsafe_eval'] ?? false) {
            $scriptSrc[] = "'unsafe-eval'";
        }

        $directives = [
            'default-src' => ["'self'"],
            'script-src' => $scriptSrc,
            'style-src' => $csp['style_src'] ?? ["'self'"],
            'font-src' => $csp['font_src'] ?? ["'self'"],
            'img-src' => $csp['img_src'] ?? ["'self'"],
            'connect-src' => $csp['connect_src'] ?? ["'self'"],
        ];

        $parts = [];
        foreach ($directives as $name => $sources) {
            $parts[] = $name.' '.implode(' ', $sources);
        }

        return implode('; ', $parts).';';
    }
}
