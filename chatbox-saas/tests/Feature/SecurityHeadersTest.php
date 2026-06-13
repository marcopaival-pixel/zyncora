<?php

namespace Tests\Feature;

use App\Http\Middleware\SecurityHeadersMiddleware;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_production_csp_excludes_unsafe_eval(): void
    {
        config([
            'app.env' => 'production',
            'security.csp.allow_unsafe_eval' => false,
        ]);

        $middleware = app(SecurityHeadersMiddleware::class);
        $csp = $this->invokeBuildCsp($middleware);

        $this->assertStringNotContainsString("'unsafe-eval'", $csp);
        $this->assertStringContainsString("script-src 'self'", $csp);
    }

    public function test_local_csp_can_include_unsafe_eval_when_enabled(): void
    {
        config([
            'security.csp.allow_unsafe_eval' => true,
        ]);

        $middleware = app(SecurityHeadersMiddleware::class);
        $csp = $this->invokeBuildCsp($middleware);

        $this->assertStringContainsString("'unsafe-eval'", $csp);
    }

    public function test_web_responses_include_csp_header(): void
    {
        config(['security.csp.allow_unsafe_eval' => false]);

        $response = $this->get('/');

        $response->assertHeader('Content-Security-Policy');
        $this->assertStringNotContainsString(
            "'unsafe-eval'",
            (string) $response->headers->get('Content-Security-Policy')
        );
    }

    protected function invokeBuildCsp(SecurityHeadersMiddleware $middleware): string
    {
        $reflection = new \ReflectionClass($middleware);
        $method = $reflection->getMethod('buildContentSecurityPolicy');

        return $method->invoke($middleware);
    }
}
