<?php

namespace Tests\Feature;

use App\Filament\Pages\Auth\CustomLogin;
use App\Filament\Pages\Billing;
use App\Filament\Resources\ConversationResource;
use App\Models\Company;
use App\Models\Conversation;
use App\Models\User;
use App\Services\RoleSyncService;
use Database\Seeders\RBACSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FilamentPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function seedRbac(): void
    {
        $this->seed(RBACSeeder::class);
    }

    public function test_login_page_renders(): void
    {
        $this->get('/admin/login')->assertOk();
    }

    public function test_agent_can_authenticate_via_filament_login(): void
    {
        $company = Company::factory()->create(['is_onboarding_completed' => true]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'email' => 'agente@test.local',
            'password' => 'password',
            'role' => User::ROLE_AGENT,
            'status' => 'active',
        ]);

        Livewire::test(CustomLogin::class)
            ->fillForm([
                'email' => $user->email,
                'password' => 'password',
            ])
            ->call('authenticate')
            ->assertHasNoFormErrors();

        $this->assertAuthenticatedAs($user);
    }

    public function test_financial_user_can_access_billing_page(): void
    {
        $this->seedRbac();

        $company = Company::factory()->create(['is_onboarding_completed' => true]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'role' => User::ROLE_FINANCIAL,
            'status' => 'active',
        ]);

        app(RoleSyncService::class)->syncUserRole($user);

        $this->actingAs($user)
            ->get(Billing::getUrl())
            ->assertOk();
    }

    public function test_agent_cannot_access_billing_page(): void
    {
        $this->seedRbac();

        $company = Company::factory()->create(['is_onboarding_completed' => true]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'role' => User::ROLE_AGENT,
            'status' => 'active',
        ]);

        app(RoleSyncService::class)->syncUserRole($user);

        $this->actingAs($user)
            ->get(Billing::getUrl())
            ->assertForbidden();
    }

    public function test_agent_can_access_conversations_list(): void
    {
        $this->seedRbac();

        $company = Company::factory()->create(['is_onboarding_completed' => true]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'role' => User::ROLE_AGENT,
            'status' => 'active',
        ]);

        app(RoleSyncService::class)->syncUserRole($user);

        Conversation::query()->create([
            'company_id' => $company->id,
            'visitor_token' => bin2hex(random_bytes(8)),
            'status' => 'waiting',
            'started_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(ConversationResource::getUrl('index'))
            ->assertOk();
    }

    public function test_platform_admin_can_access_conversations_list(): void
    {
        $this->seedRbac();

        $admin = User::factory()->create([
            'role' => User::ROLE_PLATFORM_ADMIN,
            'status' => 'active',
            'company_id' => null,
        ]);

        $this->actingAs($admin)
            ->get(ConversationResource::getUrl('index'))
            ->assertOk();
    }
}
