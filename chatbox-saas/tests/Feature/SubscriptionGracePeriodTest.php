<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Plan;
use App\Services\PlanSubscriptionService;
use App\Services\SubscriptionGracePeriodService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SubscriptionGracePeriodTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_in_grace_period_is_not_expired_yet(): void
    {
        Plan::query()->create([
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
            'slug' => 'pro-grace',
            'price' => 149.90,
            'interval' => 'month',
            'max_users' => 20,
            'max_attendants' => 10,
            'max_channels' => 5,
            'max_chatbots' => 5,
            'is_active' => true,
        ]);

        $graceCompany = Company::factory()->create([
            'plan_id' => $pro->id,
            'plan' => $pro->slug,
            'max_users' => 20,
            'expires_at' => now()->subDay(),
            'subscription_status' => 'active',
            'email' => 'financeiro@grace.test',
        ]);

        $count = app(PlanSubscriptionService::class)->expireOverdueCompanies();

        $this->assertSame(0, $count);
        $graceCompany->refresh();
        $this->assertSame('active', $graceCompany->subscription_status);
        $this->assertTrue(app(SubscriptionGracePeriodService::class)->isInGracePeriod($graceCompany));
    }

    public function test_mark_grace_period_updates_subscription_status(): void
    {
        $company = Company::factory()->create([
            'expires_at' => now()->subDay(),
            'subscription_status' => 'active',
        ]);

        $marked = app(SubscriptionGracePeriodService::class)->markGracePeriodCompanies();

        $this->assertSame(1, $marked);
        $company->refresh();
        $this->assertSame('grace', $company->subscription_status);
    }

    public function test_grace_period_notification_is_sent(): void
    {
        Mail::fake();

        $company = Company::factory()->create([
            'expires_at' => now()->subDay(),
            'subscription_status' => 'grace',
            'email' => 'billing@grace.test',
        ]);

        $sent = app(SubscriptionGracePeriodService::class)->notifyGracePeriodCompanies();

        $this->assertSame(1, $sent);
        Mail::assertSent(\App\Mail\SubscriptionGracePeriodMail::class);
    }

    public function test_process_grace_period_command_runs_successfully(): void
    {
        $this->artisan('subscriptions:process-grace-period')->assertSuccessful();
    }
}
