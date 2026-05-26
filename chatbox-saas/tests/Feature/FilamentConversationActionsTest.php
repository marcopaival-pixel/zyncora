<?php

namespace Tests\Feature;

use App\Filament\Resources\ConversationResource\Pages\ListConversations;
use App\Models\Company;
use App\Models\Conversation;
use App\Models\User;
use App\Services\RoleSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FilamentConversationActionsTest extends TestCase
{
    use RefreshDatabase;

    protected function seedRbac(): void
    {
        $this->seed(\Database\Seeders\RBACSeeder::class);
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

    public function test_agent_can_assume_unassigned_conversation(): void
    {
        $this->seedRbac();

        $company = Company::factory()->create();
        $agent = $this->agentForCompany($company);

        $conversation = Conversation::query()->create([
            'company_id' => $company->id,
            'visitor_token' => bin2hex(random_bytes(8)),
            'status' => 'waiting',
            'started_at' => now(),
        ]);

        Livewire::actingAs($agent)
            ->test(ListConversations::class)
            ->callTableAction('assume', $conversation);

        $conversation->refresh();

        $this->assertSame($agent->id, $conversation->assignee_id);
        $this->assertSame('open', $conversation->status);
    }

    public function test_agent_can_close_conversation(): void
    {
        $this->seedRbac();

        $company = Company::factory()->create();
        $agent = $this->agentForCompany($company);

        $conversation = Conversation::query()->create([
            'company_id' => $company->id,
            'visitor_token' => bin2hex(random_bytes(8)),
            'status' => 'open',
            'assignee_id' => $agent->id,
            'started_at' => now(),
        ]);

        Livewire::actingAs($agent)
            ->test(ListConversations::class)
            ->callTableAction('close', $conversation);

        $conversation->refresh();

        $this->assertSame('closed', $conversation->status);
        $this->assertNotNull($conversation->closed_at);
    }
}
