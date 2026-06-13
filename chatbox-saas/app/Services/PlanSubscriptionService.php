<?php

namespace App\Services;

use App\Models\AiConsumptionHistory;
use App\Models\Company;
use App\Models\Plan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PlanSubscriptionService
{
    public function applyPlanToCompany(Company $company, Plan $plan, ?Carbon $expiresAt = null): void
    {
        $company->update([
            'plan_id' => $plan->id,
            'plan' => $plan->slug,
            'max_users' => $plan->max_users,
            'max_attendants' => $plan->max_attendants,
            'max_channels' => $plan->max_channels,
            'max_chatbots' => $plan->max_chatbots,
            'expires_at' => $expiresAt ?? $this->defaultExpiryForPlan($plan),
            'subscription_status' => 'active',
        ]);
    }

    public function defaultExpiryForPlan(Plan $plan): Carbon
    {
        return $plan->interval === 'year'
            ? now()->addYear()
            : now()->addMonth();
    }

    public function syncStripeSubscription(Company $company, object $subscription, ?Plan $plan = null): void
    {
        $status = (string) ($subscription->status ?? 'active');
        $periodEnd = isset($subscription->current_period_end)
            ? Carbon::createFromTimestamp((int) $subscription->current_period_end)
            : null;

        $payload = [
            'subscription_status' => $status,
        ];

        if ($periodEnd !== null) {
            $payload['expires_at'] = $periodEnd;
        }

        if ($plan !== null) {
            $payload['plan_id'] = $plan->id;
            $payload['plan'] = $plan->slug;
            $payload['max_users'] = $plan->max_users;
            $payload['max_attendants'] = $plan->max_attendants;
            $payload['max_channels'] = $plan->max_channels;
            $payload['max_chatbots'] = $plan->max_chatbots;
        }

        $company->update($payload);
    }

    public function markSubscriptionCanceled(Company $company): void
    {
        $company->update([
            'subscription_status' => 'canceled',
        ]);
    }

    public function resolvePlanFromStripeMetadata(object $metadata): ?Plan
    {
        $planId = (int) ($metadata->plan_id ?? 0);

        if ($planId > 0) {
            return Plan::query()->find($planId);
        }

        $slug = (string) ($metadata->plan_slug ?? '');

        if ($slug !== '') {
            return Plan::query()->where('slug', $slug)->first();
        }

        return null;
    }

    public function findCompanyByStripeSubscriptionId(string $subscriptionId): ?Company
    {
        return Company::query()
            ->where('stripe_subscription_id', $subscriptionId)
            ->first();
    }

    public function findCompanyByMercadoPagoPreapprovalId(string $preapprovalId): ?Company
    {
        return Company::query()
            ->where('mercadopago_preapproval_id', $preapprovalId)
            ->first();
    }

    public function logPaymentFailed(object $invoice): void
    {
        Log::warning('stripe_invoice_payment_failed', [
            'invoice_id' => $invoice->id ?? null,
            'customer_id' => $invoice->customer ?? null,
            'subscription_id' => $invoice->subscription ?? null,
        ]);
    }

    public function renewCompanySubscription(Company $company): void
    {
        $plan = $company->plan_id
            ? Plan::query()->find($company->plan_id)
            : null;

        if ($plan === null && filled($company->plan)) {
            $plan = Plan::query()->where('slug', $company->plan)->first();
        }

        if ($plan === null) {
            return;
        }

        $base = $company->expires_at && $company->expires_at->isFuture()
            ? $company->expires_at->copy()
            : now();

        $expiresAt = $plan->interval === 'year'
            ? $base->copy()->addYear()
            : $base->copy()->addMonth();

        $company->update([
            'expires_at' => $expiresAt,
            'subscription_status' => 'active',
        ]);

        $this->resetAiFranchise($company, $base, $expiresAt, $plan);
    }

    public function resetAiFranchise(Company $company, Carbon $periodStart, Carbon $periodEnd, ?Plan $plan = null): void
    {
        $planModel = $plan ?? ($company->plan_id ? Plan::find($company->plan_id) : Plan::where('slug', $company->plan)->first());

        if ($company->ai_conversations_used > 0 || ($planModel?->max_ai_conversations > 0)) {
            AiConsumptionHistory::create([
                'company_id' => $company->id,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'conversations_contracted' => $planModel?->max_ai_conversations ?? 0,
                'conversations_used' => $company->ai_conversations_used ?? 0,
                'credits_purchased' => 0, // This could be queried from AiCreditPurchase in the period
            ]);
        }

        $company->update([
            'ai_conversations_used' => 0,
        ]);
    }

    public function expireOverdueCompanies(): int
    {
        $basicPlan = Plan::query()->where('slug', 'basic')->first();
        $expiredCount = 0;
        $graceDays = max(0, (int) config('chatbox.subscription_grace_period_days', 7));
        $cutoff = now()->subDays($graceDays);

        Company::query()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', $cutoff)
            ->where(function ($query) {
                $query->whereNull('subscription_status')
                    ->orWhereIn('subscription_status', ['active', 'grace']);
            })
            ->orderBy('id')
            ->chunkById(100, function ($companies) use ($basicPlan, &$expiredCount) {
                foreach ($companies as $company) {
                    $this->expireCompany($company, $basicPlan);
                    $expiredCount++;
                }
            });

        return $expiredCount;
    }

    public function expireCompany(Company $company, ?Plan $fallbackPlan = null): void
    {
        $payload = [
            'subscription_status' => 'expired',
        ];

        if ($fallbackPlan !== null) {
            $payload['plan_id'] = $fallbackPlan->id;
            $payload['plan'] = $fallbackPlan->slug;
            $payload['max_users'] = $fallbackPlan->max_users;
            $payload['max_attendants'] = $fallbackPlan->max_attendants;
            $payload['max_channels'] = $fallbackPlan->max_channels;
            $payload['max_chatbots'] = $fallbackPlan->max_chatbots;
        }

        $company->update($payload);
    }
}
