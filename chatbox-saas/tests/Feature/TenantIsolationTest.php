<?php

namespace Tests\Feature;

use App\Models\Chatbot;
use App\Models\Company;
use App\Models\User;
use App\Services\TenantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_see_chatbots_from_another_company(): void
    {
        // 1. Criar Empresa A e um Chatbot para ela
        $companyA = Company::factory()->create(['name' => 'Empresa A']);
        $chatbotA = Chatbot::create([
            'company_id' => $companyA->id,
            'name' => 'Bot da Empresa A',
            'status' => 'active',
        ]);

        // 2. Criar Empresa B e um Chatbot para ela
        $companyB = Company::factory()->create(['name' => 'Empresa B']);
        $chatbotB = Chatbot::create([
            'company_id' => $companyB->id,
            'name' => 'Bot da Empresa B',
            'status' => 'active',
        ]);

        // 3. Autenticar como usuário da Empresa A
        $userA = User::factory()->create([
            'company_id' => $companyA->id,
            'role' => User::ROLE_COMPANY_ADMIN,
        ]);

        $this->actingAs($userA);

        // O escopo global deve filtrar automaticamente os resultados
        $visibleBots = Chatbot::all();

        $this->assertCount(1, $visibleBots);
        $this->assertTrue($visibleBots->contains($chatbotA));
        $this->assertFalse($visibleBots->contains($chatbotB));
    }

    public function test_tenant_service_enforces_isolation_in_jobs(): void
    {
        $companyA = Company::factory()->create(['name' => 'Empresa A']);
        $companyB = Company::factory()->create(['name' => 'Empresa B']);

        $chatbotA = Chatbot::create([
            'company_id' => $companyA->id,
            'name' => 'Bot A',
            'status' => 'active',
        ]);

        $chatbotB = Chatbot::create([
            'company_id' => $companyB->id,
            'name' => 'Bot B',
            'status' => 'active',
        ]);

        // Simula o contexto de um Job que define o inquilino manualmente
        $tenantService = app(TenantService::class);
        $tenantService->setCompany($companyA);

        // O escopo global deve respeitar o TenantService mesmo sem usuário logado
        $visibleBots = Chatbot::all();

        $this->assertCount(1, $visibleBots);
        $this->assertSame($chatbotA->id, $visibleBots->first()->id);
        
        // Limpa contexto
        $tenantService->setCompany(null);
    }
}
