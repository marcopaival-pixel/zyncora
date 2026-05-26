<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Plan;
use App\Services\PlanSubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpireOverdueSubscriptionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_expire_overdue_downgrades_to_basic_limits(): void
    {
        $basic = Plan::query()->create([
            'name' => 'Básico',
            'slug' => 'basic',
            'price' => 49.90,
            'interval' => 'month',
            'max_users' => 2,
            'max_attendants' => 1,
            'max_channels' => 1,
            'max_chatbots' => 1,
            'is_active' => true,
        ]);

        $pro = Plan::query()->create([
            'name' => 'Pro',
            'slug' => 'pro-expire',
            'price' => 149.90,
            'interval' => 'month',
            'max_users' => 20,
            'max_attendants' => 10,
            'max_channels' => 5,
            'max_chatbots' => 5,
            'is_active' => true,
        ]);

        $expiredCompany = Company::factory()->create([
            'plan_id' => $pro->id,
            'plan' => $pro->slug,
            'max_users' => 20,
            'max_channels' => 5,
            'expires_at' => now()->subDays(8),
            'subscription_status' => 'active',
        ]);

        $activeCompany = Company::factory()->create([
            'plan_id' => $pro->id,
            'plan' => $pro->slug,
            'max_users' => 20,
            'expires_at' => now()->addMonth(),
            'subscription_status' => 'active',
        ]);

        $count = app(PlanSubscriptionService::class)->expireOverdueCompanies();

        $this->assertSame(1, $count);

        $expiredCompany->refresh();
        $activeCompany->refresh();

        $this->assertSame('expired', $expiredCompany->subscription_status);
        $this->assertSame($basic->id, $expiredCompany->plan_id);
        $this->assertSame(2, $expiredCompany->max_users);
        $this->assertSame('active', $activeCompany->subscription_status);
        $this->assertSame(20, $activeCompany->max_users);
    }

    public function test_expire_command_runs_successfully(): void
    {
        $this->artisan('subscriptions:expire-overdue')->assertSuccessful();
    }
}
