<?php

namespace Tests\Feature;

use App\Services\BillingCheckoutService;
use Tests\TestCase;

class BillingCheckoutServiceTest extends TestCase
{
    public function test_supports_stripe_and_mercadopago_drivers(): void
    {
        $service = app(BillingCheckoutService::class);

        config(['chatbox.payment_driver' => 'stripe']);
        $this->assertTrue($service->supportsCheckout());

        config(['chatbox.payment_driver' => 'mercadopago']);
        $this->assertTrue($service->supportsCheckout());

        config(['chatbox.payment_driver' => 'none']);
        $this->assertFalse($service->supportsCheckout());
    }

    public function test_checkout_success_message_varies_by_driver(): void
    {
        $service = app(BillingCheckoutService::class);

        config(['chatbox.payment_driver' => 'mercadopago']);
        $this->assertStringContainsString('Mercado Pago', $service->checkoutSuccessMessage());

        config(['chatbox.payment_driver' => 'stripe']);
        $this->assertStringContainsString('Stripe', $service->checkoutSuccessMessage());
    }
}
