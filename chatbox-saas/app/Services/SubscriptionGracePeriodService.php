<?php

namespace App\Services;

use App\Mail\SubscriptionGracePeriodMail;
use App\Models\Company;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SubscriptionGracePeriodService
{
    public function gracePeriodDays(): int
    {
        return max(0, (int) config('chatbox.subscription_grace_period_days', 7));
    }

    public function markGracePeriodCompanies(): int
    {
        $graceDays = $this->gracePeriodDays();

        if ($graceDays <= 0) {
            return 0;
        }

        return Company::query()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->where('expires_at', '>=', now()->subDays($graceDays))
            ->where(function ($query) {
                $query->whereNull('subscription_status')
                    ->orWhere('subscription_status', 'active');
            })
            ->update(['subscription_status' => 'grace']);
    }

    public function notifyGracePeriodCompanies(): int
    {
        $graceDays = $this->gracePeriodDays();

        if ($graceDays <= 0) {
            return 0;
        }

        $sentCount = 0;

        Company::query()
            ->where('subscription_status', 'grace')
            ->where(function ($query) {
                $query->whereNull('grace_period_notified_at')
                    ->orWhere('grace_period_notified_at', '<', now()->subDay());
            })
            ->orderBy('id')
            ->chunkById(50, function ($companies) use ($graceDays, &$sentCount) {
                foreach ($companies as $company) {
                    if ($this->sendGracePeriodNotice($company, $graceDays)) {
                        $company->update(['grace_period_notified_at' => now()]);
                        $sentCount++;
                    }
                }
            });

        return $sentCount;
    }

    public function sendGracePeriodNotice(Company $company, ?int $graceDays = null): bool
    {
        $graceDays = $graceDays ?? $this->gracePeriodDays();
        $recipients = app(SubscriptionExpiryNotificationService::class)->resolveRecipients($company);

        if ($recipients === []) {
            Log::warning('subscription_grace_period_skipped_no_recipients', [
                'company_id' => $company->id,
            ]);

            return false;
        }

        $graceEndsAt = $company->expires_at?->copy()->addDays($graceDays);
        $daysRemaining = $graceEndsAt
            ? max(0, (int) now()->diffInDays($graceEndsAt, false))
            : 0;

        Mail::to($recipients)->send(new SubscriptionGracePeriodMail($company, $daysRemaining, $graceEndsAt));

        return true;
    }

    public function isInGracePeriod(Company $company): bool
    {
        $graceDays = $this->gracePeriodDays();

        if ($graceDays <= 0 || $company->expires_at === null) {
            return false;
        }

        return $company->expires_at->isPast()
            && $company->expires_at->greaterThanOrEqualTo(now()->subDays($graceDays));
    }
}
