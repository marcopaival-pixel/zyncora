<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Plan;
use App\Services\MercadoPagoPaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MercadoPagoSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'chatbox.mercadopago.access_token' => 'TEST_ACCESS_TOKEN',
            'chatbox.mercadopago.currency' => 'BRL',
        ]);
    }

    public function test_create_subscription_checkout_returns_init_point(): void
    {
        Http::fake([
            'api.mercadopago.com/preapproval' => Http::response([
                'id' => 'preapproval_test_1',
                'init_point' => 'https://www.mercadopago.com.br/subscriptions/checkout?preapproval_id=preapproval_test_1',
            ], 201),
        ]);

        $plan = Plan::query()->create([
            'name' => 'Pro',
            'slug' => 'pro-mp',
            'price' => 149.90,
            'interval' => 'month',
            'max_users' => 5,
            'max_attendants' => 2,
            'max_channels' => 2,
            'max_chatbots' => 2,
            'is_active' => true,
        ]);

        $company = Company::factory()->create();
        $user = \App\Models\User::factory()->create([
            'company_id' => $company->id,
            'email' => 'financeiro@empresa.test',
        ]);

        $url = app(MercadoPagoPaymentService::class)->createSubscriptionCheckout($company, $plan, $user);

        $this->assertStringContainsString('mercadopago.com.br', $url);

        Http::assertSent(function ($request) use ($company, $plan, $user) {
            $body = $request->data();

            return $request->url() === 'https://api.mercadopago.com/preapproval'
                && $body['payer_email'] === $user->email
                && $body['external_reference'] === "company:{$company->id}:plan:{$plan->id}"
                && $body['auto_recurring']['transaction_amount'] === 149.90;
        });
    }

    public function test_authorized_preapproval_applies_plan(): void
    {
        $plan = Plan::query()->create([
            'name' => 'Pro',
            'slug' => 'pro-auth',
            'price' => 149.90,
            'interval' => 'month',
            'max_users' => 5,
            'max_attendants' => 2,
            'max_channels' => 2,
            'max_chatbots' => 2,
            'is_active' => true,
        ]);

        $company = Company::factory()->create(['plan' => 'basic']);

        Http::fake([
            'api.mercadopago.com/preapproval/preapproval_auth_1' => Http::response([
                'id' => 'preapproval_auth_1',
                'status' => 'authorized',
                'external_reference' => "company:{$company->id}:plan:{$plan->id}",
            ]),
        ]);

        app(MercadoPagoPaymentService::class)->processPreapproval('preapproval_auth_1');

        $company->refresh();

        $this->assertSame('preapproval_auth_1', $company->mercadopago_preapproval_id);
        $this->assertSame('active', $company->subscription_status);
        $this->assertSame($plan->id, $company->plan_id);
        $this->assertSame('pro-auth', $company->plan);
    }

    public function test_cancelled_preapproval_marks_subscription_canceled(): void
    {
        $company = Company::factory()->create([
            'mercadopago_preapproval_id' => 'preapproval_cancel_1',
            'subscription_status' => 'active',
        ]);

        Http::fake([
            'api.mercadopago.com/preapproval/preapproval_cancel_1' => Http::response([
                'id' => 'preapproval_cancel_1',
                'status' => 'cancelled',
                'external_reference' => "company:{$company->id}:plan:1",
            ]),
        ]);

        app(MercadoPagoPaymentService::class)->processPreapproval('preapproval_cancel_1');

        $this->assertSame('canceled', $company->fresh()->subscription_status);
    }

    public function test_parse_external_reference(): void
    {
        $parsed = app(MercadoPagoPaymentService::class)->parseExternalReference('company:10:plan:3');

        $this->assertSame(['company_id' => 10, 'plan_id' => 3], $parsed);
        $this->assertNull(app(MercadoPagoPaymentService::class)->parseExternalReference('invalid'));
    }

    public function test_approved_payment_renews_subscription_expiry(): void
    {
        $plan = Plan::query()->create([
            'name' => 'Pro',
            'slug' => 'pro-renew',
            'price' => 149.90,
            'interval' => 'month',
            'max_users' => 5,
            'max_attendants' => 2,
            'max_channels' => 2,
            'max_chatbots' => 2,
            'is_active' => true,
        ]);

        $company = Company::factory()->create([
            'plan_id' => $plan->id,
            'plan' => $plan->slug,
            'mercadopago_preapproval_id' => 'preapproval_renew_1',
            'expires_at' => now()->addDays(5),
            'subscription_status' => 'active',
        ]);

        $previousExpiry = $company->expires_at->copy();

        Http::fake([
            'api.mercadopago.com/v1/payments/payment_renew_1' => Http::response([
                'id' => 'payment_renew_1',
                'status' => 'approved',
                'preapproval_id' => 'preapproval_renew_1',
            ]),
        ]);

        app(MercadoPagoPaymentService::class)->processPayment('payment_renew_1');

        $company->refresh();

        $this->assertTrue($company->expires_at->greaterThan($previousExpiry));
        $this->assertSame('active', $company->subscription_status);
    }
}
