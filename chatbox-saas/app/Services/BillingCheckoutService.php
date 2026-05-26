<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Plan;
use App\Models\User;
use RuntimeException;

class BillingCheckoutService
{
    public function supportsCheckout(): bool
    {
        return in_array(config('chatbox.payment_driver'), ['stripe', 'mercadopago'], true);
    }

    public function checkoutUrl(Company $company, Plan $plan, User $user): string
    {
        return match (config('chatbox.payment_driver')) {
            'stripe' => app(StripePaymentService::class)->createCheckoutSession($company, $plan, $user),
            'mercadopago' => app(MercadoPagoPaymentService::class)->createSubscriptionCheckout($company, $plan, $user),
            default => throw new RuntimeException(
                'Configure PAYMENT_DRIVER=stripe ou PAYMENT_DRIVER=mercadopago para checkout em produção.'
            ),
        };
    }

    public function checkoutSuccessMessage(): string
    {
        return match (config('chatbox.payment_driver')) {
            'mercadopago' => 'O seu plano será actualizado assim que o webhook Mercado Pago for confirmado.',
            default => 'O seu plano será actualizado assim que o webhook Stripe for confirmado.',
        };
    }
}
