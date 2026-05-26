<?php

namespace Tests\Feature;

use Tests\TestCase;

class GoLiveVerifyCommandTest extends TestCase
{
    public function test_go_live_verify_passes_in_testing_environment(): void
    {
        $this->artisan('go-live:verify')
            ->assertSuccessful();
    }

    public function test_go_live_verify_fails_without_app_key(): void
    {
        config(['app.key' => null]);

        $this->artisan('go-live:verify')
            ->assertFailed();
    }

    public function test_go_live_verify_strict_treats_warnings_as_failures_in_production(): void
    {
        app()->detectEnvironment(fn () => 'production');

        config([
            'app.debug' => false,
            'chatbox.filament_registration_enabled' => false,
            'chatbox.demo_routes_enabled' => false,
            'chatbox.billing_simulation_enabled' => false,
            'chatbox.api_docs_enabled' => true,
            'chatbox.payment_driver' => 'none',
            'cors.allowed_origins' => ['*'],
        ]);

        $this->artisan('go-live:verify --strict')
            ->assertFailed();
    }
}
