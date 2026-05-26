<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiDocsTest extends TestCase
{
    public function test_api_docs_respects_runtime_config(): void
    {
        config(['chatbox.api_docs_enabled' => false]);
        $this->get('/api/docs')->assertNotFound();

        config(['chatbox.api_docs_enabled' => true]);
        $this->get('/api/docs')->assertOk();
    }

    public function test_api_docs_page_includes_swagger_ui(): void
    {
        config(['chatbox.api_docs_enabled' => true]);

        $response = $this->get('/api/docs');

        $response->assertOk();
        $this->assertStringContainsString('swagger-ui', $response->getContent());
        $this->assertStringContainsString('openapi.yaml', $response->getContent());
    }

    public function test_api_docs_basic_auth_when_configured(): void
    {
        config([
            'chatbox.api_docs_enabled' => true,
            'chatbox.api_docs_basic_auth.user' => 'docs',
            'chatbox.api_docs_basic_auth.password' => 'secret',
        ]);

        $this->get('/api/docs')->assertUnauthorized();

        $this->withBasicAuth('docs', 'secret')
            ->get('/api/docs')
            ->assertOk();
    }
}
