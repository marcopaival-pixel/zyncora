<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GoLiveSmokeService
{
    /**
     * @return array<int, array{label: string, path: string, status: int, expected: string, result: string, critical: bool}>
     */
    public function run(?string $baseUrl = null, ?string $companySlug = null): array
    {
        $checks = [
            $this->check('Health /up', 'GET', '/up', [200], $baseUrl),
            $this->check('Filament login', 'GET', '/admin/login', [200], $baseUrl),
            $this->check(
                'Registo Filament',
                'GET',
                '/admin/register',
                config('chatbox.filament_registration_enabled') ? [200] : [404],
                $baseUrl
            ),
            $this->check(
                'Rota demo',
                'GET',
                '/demo',
                config('chatbox.demo_routes_enabled') ? [200] : [404],
                $baseUrl
            ),
            $this->check('OpenAPI spec', 'GET', '/api/v1/openapi.yaml', [200], $baseUrl),
            $this->check(
                'Portal API docs',
                'GET',
                '/api/docs',
                $this->expectedApiDocsStatuses(),
                $baseUrl
            ),
        ];

        if ($companySlug) {
            $checks[] = $this->check(
                'Widget chat',
                'GET',
                '/chat/'.$companySlug,
                [200],
                $baseUrl
            );
            $checks[] = $this->check(
                'API widget conversas',
                'POST',
                '/api/v1/widget/'.$companySlug.'/conversations',
                [200, 422],
                $baseUrl
            );
        }

        return $checks;
    }

    /**
     * @param  array<int, int>  $expectedStatuses
     * @return array{label: string, path: string, status: int, expected: string, result: string, critical: bool}
     */
    protected function check(
        string $label,
        string $method,
        string $path,
        array $expectedStatuses,
        ?string $baseUrl
    ): array {
        $status = $this->fetch($method, $path, $baseUrl);
        $ok = in_array($status, $expectedStatuses, true);

        return [
            'label' => $label,
            'path' => $path,
            'status' => $status,
            'expected' => implode('|', $expectedStatuses),
            'result' => $ok ? 'pass' : 'fail',
            'critical' => ! $ok,
        ];
    }

    /**
     * @return array<int, int>
     */
    protected function expectedApiDocsStatuses(): array
    {
        if (! config('chatbox.api_docs_enabled')) {
            return [404];
        }

        $user = config('chatbox.api_docs_basic_auth.user');
        $password = config('chatbox.api_docs_basic_auth.password');

        if (filled($user) && filled($password)) {
            return [200, 401];
        }

        return [200];
    }

    protected function fetch(string $method, string $path, ?string $baseUrl): int
    {
        if ($baseUrl !== null && $baseUrl !== '') {
            $url = rtrim($baseUrl, '/').$path;

            try {
                $response = Http::timeout(15)
                    ->accept('*/*')
                    ->send(strtoupper($method), $url);

                return $response->status();
            } catch (\Throwable) {
                return 0;
            }
        }

        $request = Request::create(
            $path,
            strtoupper($method),
            [],
            [],
            [],
            ['HTTP_ACCEPT' => '*/*', 'CONTENT_TYPE' => 'application/json'],
            strtoupper($method) === 'POST' ? '{}' : null
        );
        $request->headers->set('Accept', '*/*');

        return app()->handle($request)->getStatusCode();
    }

    /**
     * @param  array<int, array{result: string, critical: bool}>  $checks
     */
    public function hasFailures(array $checks): bool
    {
        foreach ($checks as $check) {
            if ($check['result'] === 'fail' && $check['critical']) {
                return true;
            }
        }

        return false;
    }
}
