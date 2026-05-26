<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Plan;
use App\Models\User;
use Carbon\Carbon;
use RuntimeException;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class StripePaymentService
{
    public function __construct(
        protected PlanSubscriptionService $subscriptions
    ) {}

    public function isConfigured(): bool
    {
        return filled(config('chatbox.stripe.secret'));
    }

    public function createCheckoutSession(Company $company, Plan $plan, User $user): string
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Stripe não configurado (STRIPE_SECRET em falta).');
        }

        Stripe::setApiKey(config('chatbox.stripe.secret'));

        $params = [
            'mode' => 'subscription',
            'line_items' => [$this->buildLineItem($plan)],
            'metadata' => [
                'company_id' => (string) $company->id,
                'plan_id' => (string) $plan->id,
                'user_id' => (string) $user->id,
            ],
            'subscription_data' => [
                'metadata' => [
                    'company_id' => (string) $company->id,
                    'plan_id' => (string) $plan->id,
                ],
            ],
            'success_url' => url('/admin/billing').'?checkout=success&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => url('/admin/billing').'?checkout=cancelled',
        ];

        if (filled($company->stripe_customer_id)) {
            $params['customer'] = $company->stripe_customer_id;
        } else {
            $params['customer_email'] = $user->email;
        }

        $session = Session::create($params);

        return $session->url;
    }

    public function handleWebhookEvent(object $event): void
    {
        match ($event->type) {
            'checkout.session.completed' => $this->handleCheckoutCompleted($event->data->object),
            'customer.subscription.updated' => $this->handleSubscriptionUpdated($event->data->object),
            'customer.subscription.deleted' => $this->handleSubscriptionDeleted($event->data->object),
            'invoice.paid' => $this->handleInvoicePaid($event->data->object),
            'invoice.payment_failed' => $this->handlePaymentFailed($event->data->object),
            default => null,
        };
    }

    protected function buildLineItem(Plan $plan): array
    {
        if (filled($plan->stripe_price_id)) {
            return [
                'price' => $plan->stripe_price_id,
                'quantity' => 1,
            ];
        }

        $interval = in_array($plan->interval, ['month', 'year'], true) ? $plan->interval : 'month';

        return [
            'price_data' => [
                'currency' => config('chatbox.stripe.currency', 'brl'),
                'product_data' => [
                    'name' => $plan->name,
                    'description' => $plan->description,
                ],
                'unit_amount' => (int) round((float) $plan->price * 100),
                'recurring' => [
                    'interval' => $interval,
                ],
            ],
            'quantity' => 1,
        ];
    }

    protected function handleCheckoutCompleted(object $session): void
    {
        $companyId = (int) ($session->metadata->company_id ?? 0);
        $planId = (int) ($session->metadata->plan_id ?? 0);

        if ($companyId === 0 || $planId === 0) {
            return;
        }

        $company = Company::query()->find($companyId);
        $plan = Plan::query()->find($planId);

        if ($company === null || $plan === null) {
            return;
        }

        $updates = [];

        if (filled($session->customer ?? null)) {
            $updates['stripe_customer_id'] = (string) $session->customer;
        }

        if (filled($session->subscription ?? null)) {
            $updates['stripe_subscription_id'] = (string) $session->subscription;
        }

        if ($updates !== []) {
            $company->update($updates);
            $company->refresh();
        }

        $this->subscriptions->applyPlanToCompany($company, $plan);
    }

    protected function handleSubscriptionUpdated(object $subscription): void
    {
        $company = $this->subscriptions->findCompanyByStripeSubscriptionId((string) $subscription->id);

        if ($company === null) {
            $companyId = (int) ($subscription->metadata->company_id ?? 0);
            $company = $companyId > 0 ? Company::query()->find($companyId) : null;
        }

        if ($company === null) {
            return;
        }

        if (! filled($company->stripe_subscription_id)) {
            $company->update(['stripe_subscription_id' => (string) $subscription->id]);
            $company->refresh();
        }

        $plan = $this->subscriptions->resolvePlanFromStripeMetadata($subscription->metadata ?? (object) []);

        $this->subscriptions->syncStripeSubscription($company, $subscription, $plan);
    }

    protected function handleSubscriptionDeleted(object $subscription): void
    {
        $company = $this->subscriptions->findCompanyByStripeSubscriptionId((string) $subscription->id);

        if ($company === null) {
            return;
        }

        $this->subscriptions->syncStripeSubscription($company, $subscription);
        $this->subscriptions->markSubscriptionCanceled($company);
    }

    protected function handleInvoicePaid(object $invoice): void
    {
        $subscriptionId = (string) ($invoice->subscription ?? '');

        if ($subscriptionId === '') {
            return;
        }

        $company = $this->subscriptions->findCompanyByStripeSubscriptionId($subscriptionId);

        if ($company === null) {
            return;
        }

        $periodEnd = $this->resolveInvoicePeriodEnd($invoice);

        if ($periodEnd !== null) {
            $company->update([
                'expires_at' => $periodEnd,
                'subscription_status' => 'active',
            ]);

            return;
        }

        $this->subscriptions->renewCompanySubscription($company);
    }

    protected function resolveInvoicePeriodEnd(object $invoice): ?Carbon
    {
        $lines = $invoice->lines->data ?? [];

        foreach ($lines as $line) {
            $end = $line->period->end ?? null;

            if ($end !== null) {
                return Carbon::createFromTimestamp((int) $end);
            }
        }

        return null;
    }

    protected function handlePaymentFailed(object $invoice): void
    {
        $this->subscriptions->logPaymentFailed($invoice);

        $subscriptionId = (string) ($invoice->subscription ?? '');

        if ($subscriptionId === '') {
            return;
        }

        $company = $this->subscriptions->findCompanyByStripeSubscriptionId($subscriptionId);

        if ($company === null) {
            return;
        }

        $company->update(['subscription_status' => 'past_due']);
    }
}
