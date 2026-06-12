<?php

namespace Tests\Feature;

use App\Filament\Resources\ChatbotResource;
use App\Filament\Resources\ChatbotResource\Pages\CreateChatbot;
use App\Filament\Resources\ChatbotResource\Pages\EditChatbot;
use App\Filament\SuperAdmin\Resources\CompanyResource\Pages\CreateCompany;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Models\Chatbot;
use App\Models\Company;
use App\Models\User;
use App\Services\RoleSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FilamentCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function seedRbac(): void
    {
        $this->seed(\Database\Seeders\RBACSeeder::class);
    }

    public function test_platform_admin_can_create_company(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_PLATFORM_ADMIN,
            'status' => 'active',
            'company_id' => null,
        ]);

        $this->actingAs($admin);
        
        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('super-admin'));

        Livewire::test(CreateCompany::class)
            ->fillForm([
                'name' => 'Empresa CRUD Teste',
                'slug' => 'empresa-crud-teste',
                'status' => 'active',
                'plan' => 'basic',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('companies', [
            'slug' => 'empresa-crud-teste',
            'name' => 'Empresa CRUD Teste',
        ]);
    }

    public function test_company_admin_can_create_chatbot(): void
    {
        $this->seedRbac();

        $company = Company::factory()->create(['is_onboarding_completed' => true]);
        $admin = User::factory()->create([
            'company_id' => $company->id,
            'role' => User::ROLE_COMPANY_ADMIN,
            'status' => 'active',
        ]);

        app(RoleSyncService::class)->syncUserRole($admin);

        $this->actingAs($admin);

        Livewire::test(CreateChatbot::class)
            ->fillForm([
                'name' => 'Bot Comercial',
                'default_channel' => 'site',
                'status' => 'active',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('chatbots', [
            'company_id' => $company->id,
            'name' => 'Bot Comercial',
            'status' => 'active',
        ]);
    }

    public function test_company_admin_can_edit_chatbot(): void
    {
        $this->seedRbac();

        $company = Company::factory()->create(['is_onboarding_completed' => true]);
        $admin = User::factory()->create([
            'company_id' => $company->id,
            'role' => User::ROLE_COMPANY_ADMIN,
            'status' => 'active',
        ]);

        app(RoleSyncService::class)->syncUserRole($admin);

        $chatbot = Chatbot::query()->create([
            'company_id' => $company->id,
            'name' => 'Bot Original',
            'default_channel' => 'site',
            'status' => 'inactive',
        ]);

        $this->actingAs($admin);

        Livewire::test(EditChatbot::class, ['record' => $chatbot->getRouteKey()])
            ->fillForm([
                'name' => 'Bot Actualizado',
                'status' => 'active',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $chatbot->refresh();
        $this->assertSame('Bot Actualizado', $chatbot->name);
        $this->assertSame('active', $chatbot->status);
    }

    public function test_company_admin_can_create_user(): void
    {
        $this->seedRbac();

        $company = Company::factory()->create(['is_onboarding_completed' => true]);
        $admin = User::factory()->create([
            'company_id' => $company->id,
            'role' => User::ROLE_COMPANY_ADMIN,
            'status' => 'active',
        ]);

        app(RoleSyncService::class)->syncUserRole($admin);

        $this->actingAs($admin);

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Novo Agente',
                'email' => 'agente.crud@test.local',
                'password' => 'password123',
                'status' => 'active',
                'presence_status' => 'offline',
                'max_simultaneous_chats' => 5,
                'role' => User::ROLE_AGENT,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('users', [
            'company_id' => $company->id,
            'email' => 'agente.crud@test.local',
            'role' => User::ROLE_AGENT,
        ]);
    }

    public function test_company_admin_can_access_chatbot_list(): void
    {
        $this->seedRbac();

        $company = Company::factory()->create(['is_onboarding_completed' => true]);
        $admin = User::factory()->create([
            'company_id' => $company->id,
            'role' => User::ROLE_COMPANY_ADMIN,
            'status' => 'active',
        ]);

        app(RoleSyncService::class)->syncUserRole($admin);

        $response = $this->actingAs($admin)
            ->get(ChatbotResource::getUrl('index'));
            
        if ($response->status() === 302) {
            dump($response->headers->get('Location'));
        }
        
        $response->assertOk();
    }
}
