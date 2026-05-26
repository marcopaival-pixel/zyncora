<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthStatusApiTest extends TestCase
{
    public function test_health_status_hidden_without_token_config(): void
    {
        config(['chatbox.monitoring.health_check_token' => null]);

        $this->getJson('/api/v1/health/status')
            ->assertNotFound();
    }

    public function test_health_status_requires_valid_token(): void
    {
        config(['chatbox.monitoring.health_check_token' => 'secret-token']);

        $this->getJson('/api/v1/health/status')
            ->assertUnauthorized();

        $this->getJson('/api/v1/health/status?token=wrong')
            ->assertUnauthorized();
    }

    public function test_health_status_returns_json_with_token(): void
    {
        config(['chatbox.monitoring.health_check_token' => 'secret-token']);

        $this->getJson('/api/v1/health/status?token=secret-token')
            ->assertOk()
            ->assertJsonStructure([
                'status',
                'checked_at',
                'checks',
            ]);
    }
}
