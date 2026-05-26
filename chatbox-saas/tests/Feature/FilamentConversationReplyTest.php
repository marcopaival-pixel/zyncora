<?php

namespace Tests\Feature;

use App\Filament\Resources\ConversationResource\Pages\ViewConversation;
use App\Models\Company;
use App\Models\Conversation;
use App\Models\User;
use App\Services\RoleSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FilamentConversationReplyTest extends TestCase
{
    use RefreshDatabase;

    protected function seedRbac(): void
    {
        $this->seed(\Database\Seeders\RBACSeeder::class);
    }

    public function test_agent_can_send_reply_from_conversation_view(): void
    {
        $this->seedRbac();

        $company = Company::factory()->create();
        $agent = User::factory()->create([
            'company_id' => $company->id,
            'role' => User::ROLE_AGENT,
            'status' => 'active',
        ]);

        app(RoleSyncService::class)->syncUserRole($agent);

        $conversation = Conversation::query()->create([
            'company_id' => $company->id,
            'visitor_token' => bin2hex(random_bytes(8)),
            'status' => 'open',
            'assignee_id' => $agent->id,
            'started_at' => now(),
        ]);

        Livewire::actingAs($agent)
            ->test(ViewConversation::class, ['record' => $conversation->getRouteKey()])
            ->callAction('reply', data: [
                'body' => 'Olá, em que posso ajudar?',
            ]);

        $message = $conversation->messages()->first();

        $this->assertNotNull($message);
        $this->assertSame('agent', $message->sender_type);
        $this->assertSame($agent->id, $message->sender_id);
        $this->assertSame('Olá, em que posso ajudar?', $message->body);
    }
}
