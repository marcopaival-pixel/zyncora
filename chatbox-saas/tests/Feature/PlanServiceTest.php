<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use App\Services\PlanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class PlanServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_has_feature_returns_correct_value_from_config(): void
    {
        $companyPro = Company::factory()->create(['plan' => 'pro']);
        $companyBasic = Company::factory()->create(['plan' => 'basic']);

        $service = app(PlanService::class);

        // Mock config
        Config::set('chatbox.plans.features', [
            'basic' => ['whatsapp'],
            'pro' => ['whatsapp', 'ai_automation'],
        ]);

        $this->assertTrue($service->hasFeature($companyPro, 'ai_automation'));
        $this->assertFalse($service->hasFeature($companyBasic, 'ai_automation'));
        $this->assertTrue($service->hasFeature($companyBasic, 'whatsapp'));
    }

    public function test_can_add_user_respects_max_users_limit(): void
    {
        $company = Company::factory()->create(['max_users' => 1]);

        $service = app(PlanService::class);

        // Empresa nova não tem usuários ainda (além do factory se ele criar, mas aqui assumimos 0)
        $this->assertTrue($service->canAddUser($company));

        // Adiciona um usuário
        User::factory()->create(['company_id' => $company->id]);

        $this->assertFalse($service->canAddUser($company));
    }
}
