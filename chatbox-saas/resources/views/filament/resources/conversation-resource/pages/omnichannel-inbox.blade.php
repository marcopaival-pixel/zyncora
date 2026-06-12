<x-filament-panels::page>
    <div class="flex h-[75vh] bg-white dark:bg-gray-900 rounded-xl overflow-hidden shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10" x-data="{ scrollToBottom() { $refs.chatContainer.scrollTop = $refs.chatContainer.scrollHeight; } }" @message-received.window="setTimeout(() => scrollToBottom(), 50)">
        
        <!-- Sidebar: Lista de Conversas -->
        <div class="w-1/3 border-r border-gray-200 dark:border-gray-800 flex flex-col">
            <div class="p-4 border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50 flex justify-between items-center">
                <div>
                    <h3 class="font-semibold text-gray-800 dark:text-gray-200">Fila de Atendimento</h3>
                    <p class="text-xs text-gray-500">{{ count($this->conversations) }} conversas abertas</p>
                </div>
                <div wire:loading>
                    <x-filament::loading-indicator class="h-4 w-4 text-primary-500" />
                </div>
            </div>
            <div class="flex-1 overflow-y-auto p-2 space-y-2">
                @forelse ($this->conversations as $conv)
                <div wire:click="selectConversation({{ $conv->id }})" class="p-3 border rounded-lg cursor-pointer transition-colors {{ $activeConversationId === $conv->id ? 'bg-primary-50 dark:bg-primary-500/10 border-primary-500 dark:border-primary-500' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50' }}">
                    <div class="flex justify-between items-start">
                        <span class="font-medium text-sm text-gray-900 dark:text-white">{{ $conv->client_name ?: 'Visitante #' . $conv->id }}</span>
                        <span class="text-xs text-gray-500">{{ $conv->updated_at->diffForHumans(null, true, true) }}</span>
                    </div>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1 truncate">{{ $conv->messages->last()?->body ?? 'Iniciou conversa...' }}</p>
                    <div class="mt-2 flex gap-1 items-center justify-between">
                        <span class="inline-flex items-center rounded-md px-2 py-1 text-[10px] font-medium ring-1 ring-inset {{ $conv->status === 'waiting' ? 'bg-warning-50 text-warning-700 ring-warning-600/20' : 'bg-success-50 text-success-700 ring-success-600/20' }}">
                            {{ $conv->status === 'waiting' ? 'Aguardando' : 'Em Atendimento' }}
                        </span>
                        @if($conv->ai_score)
                        <span class="text-[10px] text-gray-500">IA Score: {{ $conv->ai_score }}%</span>
                        @endif
                    </div>
                </div>
                @empty
                <div class="flex flex-col items-center justify-center h-full text-gray-500">
                    <x-filament::icon icon="heroicon-o-inbox" class="h-8 w-8 mb-2 opacity-50" />
                    <p class="text-sm">A fila está vazia.</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Área Principal: Chat -->
        <div class="flex-1 flex flex-col bg-gray-50 dark:bg-gray-900 relative">
            @if($this->activeConversation)
                <!-- Header do Chat -->
                <div class="p-4 border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-800/50 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center text-primary-700 dark:text-primary-300 font-bold uppercase">
                            {{ substr($this->activeConversation->client_name ?? 'V', 0, 1) }}
                        </div>
                        <div>
                            <h2 class="font-semibold text-gray-900 dark:text-white">{{ $this->activeConversation->client_name ?: 'Visitante #' . $this->activeConversation->id }}</h2>
                            <p class="text-xs {{ $this->activeConversation->status === 'waiting' ? 'text-warning-600' : 'text-success-600' }}">
                                {{ $this->activeConversation->status === 'waiting' ? 'Aguardando...' : 'Em atendimento por ' . ($this->activeConversation->assignee->name ?? 'Mim') }}
                            </p>
                        </div>
                    </div>
                    <div>
                        @if(!$this->activeConversation->assignee_id)
                        <x-filament::button wire:click="assumeConversation" color="success" size="sm">
                            Assumir Ticket
                        </x-filament::button>
                        @endif
                    </div>
                </div>

                <!-- Corpo do Chat -->
                <div x-ref="chatContainer" class="flex-1 p-4 overflow-y-auto space-y-4" style="scroll-behavior: smooth;">
                    @foreach($this->activeConversation->messages as $msg)
                        <div class="flex {{ $msg->sender_type === 'visitor' ? 'justify-start' : 'justify-end' }}">
                            <div class="max-w-md shadow-sm border rounded-lg p-3 {{ $msg->sender_type === 'visitor' ? 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700' : 'bg-primary-50 dark:bg-primary-900/20 border-primary-100 dark:border-primary-800' }}">
                                @if($msg->sender_type === 'agent' && $msg->sender_id !== auth()->id())
                                    <span class="text-[10px] font-semibold text-primary-600 block mb-1">{{ $msg->sender->name ?? 'Agente' }}</span>
                                @elseif($msg->sender_type === 'bot')
                                    <span class="text-[10px] font-semibold text-success-600 block mb-1">Zynkora AI</span>
                                @endif
                                <p class="text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap">{{ $msg->body }}</p>
                                <span class="text-[10px] text-gray-400 mt-1 block text-right">{{ $msg->created_at->format('H:i') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Área de Input -->
                <div class="p-4 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                    <form wire:submit.prevent="sendMessage" class="flex gap-2">
                        <x-filament::input.wrapper class="flex-1">
                            <x-filament::input wire:model="newMessage" type="text" placeholder="Digite sua mensagem para o visitante..." required autocomplete="off" />
                        </x-filament::input.wrapper>
                        <x-filament::button type="submit" wire:loading.attr="disabled">
                            Enviar
                        </x-filament::button>
                    </form>
                </div>
            @else
                <!-- Estado Vazio -->
                <div class="flex flex-col items-center justify-center h-full text-gray-400">
                    <x-filament::icon icon="heroicon-o-chat-bubble-left-right" class="h-16 w-16 mb-4 opacity-30" />
                    <h3 class="text-lg font-medium text-gray-500 dark:text-gray-400">Nenhuma conversa selecionada</h3>
                    <p class="text-sm">Selecione um atendimento na fila ao lado para iniciar.</p>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
