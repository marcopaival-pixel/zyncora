<?php

namespace App\Filament\Resources\ConversationResource\Pages;

use App\Filament\Resources\ConversationResource;
use Filament\Resources\Pages\Page;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use App\Events\MessageCreated;

class OmnichannelInbox extends Page
{
    protected static string $resource = ConversationResource::class;

    protected static string $view = 'filament.resources.conversation-resource.pages.omnichannel-inbox';

    public ?int $activeConversationId = null;
    public string $newMessage = '';
    public int $companyId;

    public function mount()
    {
        $this->companyId = Auth::user()->company_id;
    }

    public function getListeners()
    {
        return [
            "echo-private:company.{$this->companyId},.message.created" => 'handleNewMessage',
        ];
    }

    public function handleNewMessage($event)
    {
        // Força o re-render das conversas e das mensagens se o chat estiver ativo
        if ($this->activeConversationId && $this->activeConversationId == $event['message']['conversation_id']) {
            $this->dispatch('message-received'); // Scroll to bottom
        }
    }

    #[Computed]
    public function conversations()
    {
        $user = Auth::user();
        $query = Conversation::where('company_id', $user->company_id)
            ->whereIn('status', ['waiting', 'open'])
            ->orderBy('updated_at', 'desc');

        if ($user->isAgent()) {
            $departmentIds = $user->departments()->pluck('departments.id')->toArray();

            $query->where(function ($q) use ($user, $departmentIds) {
                // Conversas já assumidas pelo agente
                $q->where('assignee_id', $user->id)
                  // OU conversas novas (sem assignee)
                  ->orWhere(function ($sub) use ($departmentIds) {
                      $sub->whereNull('assignee_id');
                      if (!empty($departmentIds)) {
                          // Se o agente tem departamentos, ele vê as nulas E que são do departamento dele (ou sem departamento)
                          $sub->where(function ($dSub) use ($departmentIds) {
                              $dSub->whereIn('department_id', $departmentIds)
                                   ->orWhereNull('department_id');
                          });
                      }
                  });
            });
        }

        return $query->get();
    }

    #[Computed]
    public function activeConversation()
    {
        if (!$this->activeConversationId) return null;
        
        return Conversation::with(['messages' => function ($q) {
            $q->orderBy('created_at', 'asc');
        }])->find($this->activeConversationId);
    }

    public function selectConversation(int $id)
    {
        $this->activeConversationId = $id;
        $this->dispatch('message-received'); // Scroll to bottom
    }

    public function assumeConversation()
    {
        $conversation = $this->activeConversation;
        if ($conversation && !$conversation->assignee_id) {
            $conversation->update([
                'assignee_id' => Auth::id(),
                'status' => 'open'
            ]);
        }
    }

    public function sendMessage()
    {
        $text = trim($this->newMessage);
        if (empty($text) || !$this->activeConversationId) return;

        $conversation = $this->activeConversation;

        // Assumir o ticket implicitamente
        if (!$conversation->assignee_id) {
            $conversation->update([
                'assignee_id' => Auth::id(),
                'status' => 'open'
            ]);
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'agent',
            'sender_id' => Auth::id(),
            'message_type' => 'text',
            'body' => $text,
            'sent_at' => now(),
        ]);

        $conversation->touch(); // Força a subida na fila

        $this->newMessage = '';

        // Disparar o evento para o frontend do visitante
        broadcast(new MessageCreated($message));

        $this->dispatch('message-received'); // Scroll to bottom
    }

    public function transferConversation(?int $departmentId, ?int $userId = null)
    {
        $conversation = $this->activeConversation;
        if (!$conversation) return;

        $conversation->update([
            'department_id' => $departmentId,
            'assignee_id' => $userId, // Se nulo, volta pra fila
            'status' => $userId ? 'open' : 'waiting'
        ]);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'system',
            'message_type' => 'text',
            'body' => 'A conversa foi transferida para outro setor/agente.',
            'sent_at' => now(),
        ]);

        $this->activeConversationId = null;
        \Filament\Notifications\Notification::make()
            ->title('Conversa Transferida')
            ->success()
            ->send();
    }
}
