<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Conversation;
use App\Models\User;
use App\Services\RoleSyncService;
use Database\Seeders\RBACSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConversationPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function seedRbac(): void
    {
        $this->seed(RBACSeeder::class);
    }

    protected function agentForCompany(Company $company): User
    {
        $user = User::factory()->create([
            'company_id' => $company->id,
            'role' => User::ROLE_AGENT,
            'status' => 'active',
        ]);

        app(RoleSyncService::class)->syncUserRole($user);

        return $user;
    }

    public function test_agent_can_view_own_company_conversation(): void
    {
        $this->seedRbac();

        $company = Company::factory()->create();
        $agent = $this->agentForCompany($company);

        $conversation = Conversation::query()->create([
            'company_id' => $company->id,
            'visitor_token' => bin2hex(random_bytes(8)),
            'status' => 'open',
            'started_at' => now(),
        ]);

        $this->actingAs($agent, 'sanctum')
            ->getJson("/api/v1/conversations/{$conversation->id}")
            ->assertOk();
    }

    public function test_agent_cannot_view_other_company_conversation(): void
    {
        $this->seedRbac();

        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $agent = $this->agentForCompany($companyA);

        $conversation = Conversation::query()->withoutGlobalScope('company')->create([
            'company_id' => $companyB->id,
            'visitor_token' => bin2hex(random_bytes(8)),
            'status' => 'open',
            'started_at' => now(),
        ]);

        $this->assertFalse($agent->can('view', $conversation));
    }

    public function test_platform_admin_can_view_any_conversation(): void
    {
        $this->seedRbac();

        $company = Company::factory()->create();
        $admin = User::factory()->create([
            'role' => User::ROLE_PLATFORM_ADMIN,
            'status' => 'active',
            'company_id' => null,
        ]);

        $conversation = Conversation::query()->create([
            'company_id' => $company->id,
            'visitor_token' => bin2hex(random_bytes(8)),
            'status' => 'open',
            'started_at' => now(),
        ]);

        $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/conversations/{$conversation->id}")
            ->assertOk();
    }
}
