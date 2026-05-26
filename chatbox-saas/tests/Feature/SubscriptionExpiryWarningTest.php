<?php

namespace Tests\Feature;

use App\Mail\SubscriptionExpiringMail;
use App\Models\Company;
use App\Models\User;
use App\Services\SubscriptionExpiryNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SubscriptionExpiryWarningTest extends TestCase
{
    use RefreshDatabase;

    public function test_warn_expiring_sends_email_to_company_contact(): void
    {
        Mail::fake();

        config(['chatbox.subscription_expiry_warning_days' => 7]);

        $company = Company::factory()->create([
            'email' => 'financeiro@empresa.test',
            'expires_at' => now()->addDays(3),
            'subscription_status' => 'active',
        ]);

        $count = app(SubscriptionExpiryNotificationService::class)->warnExpiringCompanies();

        $this->assertSame(1, $count);

        Mail::assertSent(SubscriptionExpiringMail::class, function (SubscriptionExpiringMail $mail) use ($company) {
            return $mail->company->is($company) && $mail->daysRemaining >= 2;
        });

        $this->assertNotNull($company->fresh()->expiry_warning_sent_at);
    }

    public function test_warn_expiring_skips_already_notified_within_24h(): void
    {
        Mail::fake();

        Company::factory()->create([
            'email' => 'financeiro@empresa.test',
            'expires_at' => now()->addDays(3),
            'subscription_status' => 'active',
            'expiry_warning_sent_at' => now()->subHours(2),
        ]);

        $count = app(SubscriptionExpiryNotificationService::class)->warnExpiringCompanies();

        $this->assertSame(0, $count);
        Mail::assertNothingSent();
    }

    public function test_resolve_recipients_includes_financial_users(): void
    {
        $company = Company::factory()->create(['email' => null]);
        User::factory()->create([
            'company_id' => $company->id,
            'email' => 'fin@test.local',
            'role' => User::ROLE_FINANCIAL,
            'status' => 'active',
        ]);

        $recipients = app(SubscriptionExpiryNotificationService::class)->resolveRecipients($company);

        $this->assertContains('fin@test.local', $recipients);
    }

    public function test_warn_command_runs_successfully(): void
    {
        Mail::fake();

        $this->artisan('subscriptions:warn-expiring')->assertSuccessful();
    }
}
