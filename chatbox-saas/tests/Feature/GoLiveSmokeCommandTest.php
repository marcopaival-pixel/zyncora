<?php

namespace Tests\Feature;

use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoLiveSmokeCommandTest extends TestCase
{
    use RefreshDatabase;
    public function test_go_live_smoke_passes_internally(): void
    {
        $this->artisan('go-live:smoke')
            ->assertSuccessful();
    }

    public function test_go_live_smoke_respects_demo_disabled(): void
    {
        config(['chatbox.demo_routes_enabled' => false]);

        $this->artisan('go-live:smoke')
            ->assertSuccessful();
    }

    public function test_go_live_smoke_with_company_slug(): void
    {
        $company = Company::factory()->create(['slug' => 'smoke-test-co', 'is_onboarding_completed' => true, 'status' => 'active']);


        $this->artisan('go-live:smoke', ['--company-slug' => $company->slug])
            ->assertSuccessful();
    }
}
