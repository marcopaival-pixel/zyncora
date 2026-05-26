<?php

namespace App\Services;

use App\Mail\SubscriptionExpiringMail;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SubscriptionExpiryNotificationService
{
    public function warnExpiringCompanies(?int $daysAhead = null): int
    {
        $daysAhead = $daysAhead ?? (int) config('chatbox.subscription_expiry_warning_days', 7);
        $sentCount = 0;

        Company::query()
            ->whereNotNull('expires_at')
            ->where('expires_at', '>', now())
            ->where('expires_at', '<=', now()->addDays($daysAhead))
            ->where(function ($query) {
                $query->whereNull('subscription_status')
                    ->orWhere('subscription_status', 'active');
            })
            ->where(function ($query) {
                $query->whereNull('expiry_warning_sent_at')
                    ->orWhere('expiry_warning_sent_at', '<', now()->subDay());
            })
            ->orderBy('id')
            ->chunkById(50, function ($companies) use (&$sentCount) {
                foreach ($companies as $company) {
                    if ($this->sendExpiryWarning($company)) {
                        $company->update(['expiry_warning_sent_at' => now()]);
                        $sentCount++;
                    }
                }
            });

        return $sentCount;
    }

    public function sendExpiryWarning(Company $company): bool
    {
        $recipients = $this->resolveRecipients($company);

        if ($recipients === []) {
            Log::warning('subscription_expiry_warning_skipped_no_recipients', [
                'company_id' => $company->id,
            ]);

            return false;
        }

        $daysRemaining = max(0, (int) now()->diffInDays($company->expires_at, false));

        Mail::to($recipients)->send(new SubscriptionExpiringMail($company, $daysRemaining));

        return true;
    }

    /**
     * @return array<int, string>
     */
    public function resolveRecipients(Company $company): array
    {
        $emails = [];

        if (filled($company->email)) {
            $emails[] = $company->email;
        }

        $roleEmails = $company->users()
            ->where('status', 'active')
            ->whereIn('role', [User::ROLE_FINANCIAL, User::ROLE_COMPANY_ADMIN, User::ROLE_MANAGER])
            ->pluck('email')
            ->all();

        return array_values(array_unique(array_filter([...$emails, ...$roleEmails])));
    }
}
