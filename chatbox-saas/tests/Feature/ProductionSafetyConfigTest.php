<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionSafetyConfigTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_middleware_respects_runtime_config(): void
    {
        config(['chatbox.demo_routes_enabled' => false]);
        $this->get('/demo')->assertNotFound();
        $this->postJson('/api/demo-chat', ['message' => 'test'])->assertNotFound();

        config(['chatbox.demo_routes_enabled' => true]);
        $this->get('/demo')->assertOk();
    }

    public function test_go_live_config_keys_are_defined(): void
    {
        $this->assertNotNull(config('chatbox.filament_registration_enabled'));
        $this->assertNotNull(config('chatbox.demo_routes_enabled'));
        $this->assertNotNull(config('chatbox.billing_simulation_enabled'));
        $this->assertSame('none', config('chatbox.payment_driver'));
        $this->assertNotNull(config('chatbox.api_docs_enabled'));
    }
}
