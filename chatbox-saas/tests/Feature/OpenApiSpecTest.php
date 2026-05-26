<?php

namespace Tests\Feature;

use Tests\TestCase;

class OpenApiSpecTest extends TestCase
{
    public function test_openapi_yaml_is_publicly_accessible(): void
    {
        $response = $this->get('/api/v1/openapi.yaml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/yaml; charset=utf-8');
        $this->assertStringContainsString('openapi: 3.0.3', $response->getContent());
        $this->assertStringContainsString('/widget/{slug}/config', $response->getContent());
    }
}
