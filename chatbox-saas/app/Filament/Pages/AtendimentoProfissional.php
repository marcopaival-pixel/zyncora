<?php

namespace App\Filament\Pages;

use App\Models\Conversation;
use App\Models\InternalNote;
use App\Models\Message;
use App\Models\QuickReply;
use App\Models\User;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class AtendimentoProfissional extends Page
{
    use WithFileUploads;
    use WithPagination;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static string $view = 'filament.pages.atendimento-profissional';

    protected static ?string $title = 'Atendimento profissional';

    protected static ?string $navigationLabel = 'Atendimento profissional';

    protected static ?string $navigationGroup = 'Atendimento';

    protected static ?int $navigationSort = -1;

    // Propriedades Livewire para Reatividade
    public $search = '';

    public $activeConversationId = null;

    public $newMessage = '';

    // Suporte a Anexos e Notas Internas
    public $attachment = null;

    public $isInternal = false;

    // Suporte a Respostas Rápidas
    public $showQuickReplies = false;

    public $quickReplySearch = '';

    /** Quantas mensagens recentes carregar na conversa ativa (aumenta com "Carregar anteriores"). */
    public int $messageWindow = 80;

    /** Cache por pedido HTTP para não duplicar a query entre computeds. */
    private ?array $messagesSliceMemo = null;

    public static function canAccess(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user?->canChat() ?? false;
    }

    public function getSubheading(): ?string
    {
        return 'Fila de conversas, histórico e detalhes do contacto num único ecrã.';
    }

    public function mount(): void
    {
        $firstConv = $this->conversationsQuery()->first();
        if ($firstConv) {
            $this->activeConversationId = $firstConv->id;
        }
    }

    public function updatedSearch(?string $value): void
    {
        $this->resetPage('convListPage');
    }

    /**
     * Conversas que o utilizador atual pode operar.
     */
    protected function visibleConversationsQuery(): Builder
    {
        /** @var User|null $user */
        $user = Auth::user();

        return Conversation::query()
            ->when($user && $user->isAgent(), function ($query) use ($user) {
                $query->where(function ($q) use ($user) {
                    $q->where('assignee_id', $user->id)
                        ->orWhereNull('assignee_id');
                });
            })
            ->with(['assignee', 'channel'])
            ->orderBy('updated_at', 'desc');
    }

    /**
     * Lista lateral: mesma query usada na paginação (sem carregar todas as conversas).
     */
    protected function conversationsQuery(): Builder
    {
        return $this->visibleConversationsQuery()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('client_name', 'like', '%'.$this->search.'%')
                        ->orWhere('client_phone', 'like', '%'.$this->search.'%');
                });
            });
    }

    // Identificar digitação de "/" (Slash Command)
    public function updatedNewMessage($value)
    {
        if (Str::startsWith($value, '/')) {
            $this->showQuickReplies = true;
            $this->quickReplySearch = substr($value, 1);
        } else {
            $this->showQuickReplies = false;
        }
    }

    // Buscar respostas rápidas
    public function getQuickRepliesProperty()
    {
        if (! $this->showQuickReplies) {
            return collect();
        }

        return QuickReply::query()
            ->when($this->quickReplySearch, function ($q) {
                $q->where('shortcut', 'like', '%'.$this->quickReplySearch.'%')
                    ->orWhere('message', 'like', '%'.$this->quickReplySearch.'%');
            })
            ->take(6)
            ->get();
    }

    // Inserir a resposta rapida na caixa de texto
    public function insertQuickReply($text)
    {
        $this->newMessage = $text;
        $this->showQuickReplies = false;
    }

    public function getConversationsProperty()
    {
        return $this->conversationsQuery()->paginate(30, ['*'], 'convListPage');
    }

    /**
     * Conversa ativa sem eager load de todas as mensagens (usa {@see activeMessages}).
     */
    public function getActiveConversationProperty()
    {
        if (! $this->activeConversationId) {
            return null;
        }

        return $this->visibleConversationsQuery()
            ->with(['company'])
            ->find($this->activeConversationId);
    }

    // Ouvir eventos em tempo real para atualizar o chat e a sidebar
    public function getListeners(): array
    {
        /** @var User|null $user */
        $user = Auth::user();
        $companyId = $user?->company_id;
        $listeners = [];

        if ($companyId) {
            // Ouvir atualizações da empresa (novos tickets, novas mensagens globais para mover sidebar)
            $listeners["echo-private:company.{$companyId},.message.created"] = '$refresh';
        }

        if ($this->activeConversationId) {
            // Ouvir mensagens específicas da conversa aberta
            $listeners["echo-private:conversation.{$this->activeConversationId},.message.created"] = '$refresh';
        }

        return $listeners;
    }

    // Ação para trocar de conversa na sidebar
    public function selectConversation($id)
    {
        $conversation = $this->visibleConversationsQuery()->find($id);

        if (! $conversation) {
            return;
        }

        $this->activeConversationId = $id;
        $this->messageWindow = 80;
        $this->messagesSliceMemo = null;
        $this->attachment = null;
        $this->isInternal = false;
    }

    public function loadMoreMessages(): void
    {
        $this->messageWindow = min($this->messageWindow + 80, 2000);
        $this->messagesSliceMemo = null;
    }

    /**
     * Últimas N mensagens (cronológicas), com deteção de mais histórico disponível.
     *
     * @return array{messages: Collection, has_more: bool}
     */
    private function messagesSlice(): array
    {
        if ($this->messagesSliceMemo !== null) {
            return $this->messagesSliceMemo;
        }

        if (! $this->activeConversationId) {
            return $this->messagesSliceMemo = [
                'messages' => collect(),
                'has_more' => false,
            ];
        }

        if (! $this->visibleConversationsQuery()->whereKey($this->activeConversationId)->exists()) {
            return $this->messagesSliceMemo = [
                'messages' => collect(),
                'has_more' => false,
            ];
        }

        $rows = Message::query()
            ->where('conversation_id', $this->activeConversationId)
            ->orderByDesc('id')
            ->limit($this->messageWindow + 1)
            ->get();

        $hasMore = $rows->count() > $this->messageWindow;
        if ($hasMore) {
            $rows = $rows->take($this->messageWindow);
        }

        return $this->messagesSliceMemo = [
            'messages' => $rows->sortBy('id')->values(),
            'has_more' => $hasMore,
        ];
    }

    public function getActiveMessagesProperty()
    {
        return $this->messagesSlice()['messages'];
    }

    public function getActiveMessagesHasMoreProperty(): bool
    {
        return $this->messagesSlice()['has_more'];
    }

    // Ação para alternar modo interno (Sussurro)
    public function toggleInternal()
    {
        $this->isInternal = ! $this->isInternal;
    }

    // Ação para enviar mensagem
    public function sendMessage()
    {
        if ((trim($this->newMessage) === '' && ! $this->attachment) || ! $this->activeConversationId) {
            return;
        }

        $conversation = $this->visibleConversationsQuery()->find($this->activeConversationId);

        if (! $conversation) {
            return;
        }

        $this->validate([
            'newMessage' => ['nullable', 'string', 'max:4000'],
            'attachment' => ['nullable', 'file', 'max:10240', 'mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,txt,csv'],
        ]);

        $path = null;
        $type = 'text';

        if ($this->attachment) {
            $path = $this->attachment->store('attachments/'.$conversation->company_id, 'public');
            $type = 'file';

            // Tentar identificar se é imagem
            $mime = $this->attachment->getMimeType();
            if (str_contains($mime, 'image')) {
                $type = 'image';
            }
        }

        // Criar mensagem
        $conversation->messages()->create([
            'sender_type' => $this->isInternal ? 'internal' : 'agent',
            'sender_id' => Auth::id(),
            'body' => trim($this->newMessage),
            'message_type' => $type,
            'attachment_path' => $path,
            'sent_at' => now(),
        ]);

        // Se for nota interna, também espelhamos no modelo de InternalNote para relatórios futuros
        if ($this->isInternal) {
            InternalNote::create([
                'conversation_id' => $this->activeConversationId,
                'user_id' => Auth::id(),
                'content' => trim($this->newMessage),
            ]);
        }

        // Atualizar timestamp da conversa para subir na fila
        $conversation->touch();

        $this->newMessage = '';
        $this->attachment = null;
        $this->isInternal = false;
    }
}
