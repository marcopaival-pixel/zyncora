<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\LgpdConsent;
use App\Support\LgpdConsentToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LgpdConsentTest extends TestCase
{
    use RefreshDatabase;

    public function test_consent_requires_valid_company_token(): void
    {
        $company = Company::query()->create([
            'name' => 'Empresa LGPD',
            'slug' => 'empresa-lgpd',
            'status' => 'active',
        ]);

        $this->postJson(route('lgpd.consent'), [
            'company_id' => $company->id,
            'consent_token' => 'invalid-token',
            'consent' => true,
        ])->assertUnprocessable();

        $this->assertSame(0, LgpdConsent::query()->count());
    }

    public function test_consent_is_recorded_with_valid_company_token(): void
    {
        $company = Company::query()->create([
            'name' => 'Empresa LGPD',
            'slug' => 'empresa-lgpd-valid',
            'status' => 'active',
        ]);

        $this->postJson(route('lgpd.consent'), [
            'company_id' => $company->id,
            'consent_token' => LgpdConsentToken::make($company),
            'customer_id' => 'visitor-123',
            'consent' => true,
        ])->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('lgpd_consents', [
            'company_id' => $company->id,
            'customer_id' => 'visitor-123',
            'consent_given' => true,
        ]);
    }
}
