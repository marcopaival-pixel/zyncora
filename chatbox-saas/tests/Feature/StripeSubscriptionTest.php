<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Plan;
use App\Services\PlanSubscriptionService;
use App\Services\StripePaymentService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StripeSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_completed_applies_plan_and_stores_stripe_ids(): void
    {
        $plan = Plan::query()->create([
            'name' => 'Pro',
            'slug' => 'pro-test',
            'price' => 99.90,
            'interval' => 'month',
            'max_users' => 5,
            'max_attendants' => 2,
            'max_channels' => 2,
            'max_chatbots' => 2,
            'is_active' => true,
        ]);

        $company = Company::factory()->create(['plan' => 'basic', 'plan_id' => null]);

        $session = (object) [
            'metadata' => (object) [
                'company_id' => (string) $company->id,
                'plan_id' => (string) $plan->id,
            ],
            'customer' => 'cus_test123',
            'subscription' => 'sub_test456',
        ];

        $event = (object) [
            'type' => 'checkout.session.completed',
            'data' => (object) ['object' => $session],
        ];

        app(StripePaymentService::class)->handleWebhookEvent($event);

        $company->refresh();

        $this->assertSame('cus_test123', $company->stripe_customer_id);
        $this->assertSame('sub_test456', $company->stripe_subscription_id);
        $this->assertSame('active', $company->subscription_status);
        $this->assertSame($plan->id, $company->plan_id);
        $this->assertSame('pro-test', $company->plan);
    }

    public function test_subscription_updated_syncs_period_end(): void
    {
        $plan = Plan::query()->create([
            'name' => 'Pro',
            'slug' => 'pro-sync',
            'price' => 99.90,
            'interval' => 'month',
            'max_users' => 5,
            'max_attendants' => 2,
            'max_channels' => 2,
            'max_chatbots' => 2,
            'is_active' => true,
        ]);

        $periodEnd = now()->addDays(30)->timestamp;

        $company = Company::factory()->create([
            'stripe_subscription_id' => 'sub_sync789',
            'plan_id' => $plan->id,
            'plan' => $plan->slug,
        ]);

        $subscription = (object) [
            'id' => 'sub_sync789',
            'status' => 'active',
            'current_period_end' => $periodEnd,
            'metadata' => (object) ['plan_id' => (string) $plan->id],
        ];

        $event = (object) [
            'type' => 'customer.subscription.updated',
            'data' => (object) ['object' => $subscription],
        ];

        app(StripePaymentService::class)->handleWebhookEvent($event);

        $company->refresh();

        $this->assertSame('active', $company->subscription_status);
        $this->assertNotNull($company->expires_at);
        $this->assertTrue($company->expires_at->equalTo(Carbon::createFromTimestamp($periodEnd)));
    }

    public function test_subscription_deleted_marks_canceled(): void
    {
        $company = Company::factory()->create([
            'stripe_subscription_id' => 'sub_del999',
            'subscription_status' => 'active',
        ]);

        $subscription = (object) [
            'id' => 'sub_del999',
            'status' => 'canceled',
            'current_period_end' => now()->addDays(5)->timestamp,
            'metadata' => (object) [],
        ];

        $event = (object) [
            'type' => 'customer.subscription.deleted',
            'data' => (object) ['object' => $subscription],
        ];

        app(StripePaymentService::class)->handleWebhookEvent($event);

        $this->assertSame('canceled', $company->fresh()->subscription_status);
    }

    public function test_plan_subscription_service_year_interval(): void
    {
        $plan = Plan::query()->create([
            'name' => 'Anual',
            'slug' => 'yearly',
            'price' => 999.00,
            'interval' => 'year',
            'max_users' => 1,
            'max_attendants' => 1,
            'max_channels' => 1,
            'max_chatbots' => 1,
            'is_active' => true,
        ]);

        $company = Company::factory()->create();

        app(PlanSubscriptionService::class)->applyPlanToCompany($company, $plan);

        $company->refresh();

        $this->assertTrue($company->expires_at->greaterThan(now()->addMonths(11)));
    }

    public function test_invoice_paid_renews_subscription_expiry(): void
    {
        $plan = Plan::query()->create([
            'name' => 'Pro',
            'slug' => 'pro-invoice',
            'price' => 99.90,
            'interval' => 'month',
            'max_users' => 5,
            'max_attendants' => 2,
            'max_channels' => 2,
            'max_chatbots' => 2,
            'is_active' => true,
        ]);

        $periodEnd = now()->addDays(35)->timestamp;

        $company = Company::factory()->create([
            'stripe_subscription_id' => 'sub_invoice_1',
            'plan_id' => $plan->id,
            'plan' => $plan->slug,
            'expires_at' => now()->addDays(5),
            'subscription_status' => 'active',
        ]);

        $invoice = (object) [
            'subscription' => 'sub_invoice_1',
            'lines' => (object) [
                'data' => [
                    (object) [
                        'period' => (object) ['end' => $periodEnd],
                    ],
                ],
            ],
        ];

        $event = (object) [
            'type' => 'invoice.paid',
            'data' => (object) ['object' => $invoice],
        ];

        app(StripePaymentService::class)->handleWebhookEvent($event);

        $company->refresh();

        $this->assertTrue($company->expires_at->equalTo(Carbon::createFromTimestamp($periodEnd)));
        $this->assertSame('active', $company->subscription_status);
    }
}
