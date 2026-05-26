@php
    $convStatus = static function (?string $s): array {
        return match ($s) {
            'open' => ['label' => 'Aberta', 'class' => 'border-emerald-500/30 bg-emerald-500/10 text-emerald-400'],
            'waiting' => ['label' => 'Fila de Espera', 'class' => 'border-amber-500/30 bg-amber-500/10 text-amber-400'],
            'closed' => ['label' => 'Encerrada', 'class' => 'border-white/10 bg-white/5 text-slate-500'],
            default => ['label' => $s ? strtoupper($s) : '—', 'class' => 'border-white/10 bg-white/5 text-slate-400'],
        };
    };

    $contactInitials = static function (?string $name): string {
        $parts = preg_split('/\s+/', trim((string) $name)) ?: [];
        $letters = array_map(
            static fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)),
            array_slice(array_filter($parts), 0, 2),
        );

        return implode('', $letters) ?: '?';
    };
@endphp

<x-filament-panels::page>
    <div
        class="flex min-h-[600px] h-[min(85vh,calc(100vh-10rem))] flex-col overflow-hidden rounded-[2.5rem] border border-white/10 bg-[#020617] shadow-2xl relative lg:flex-row"
    >
        <!-- Background Accents -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden opacity-20">
            <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] rounded-full bg-primary-500/10 blur-[100px]"></div>
            <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] rounded-full bg-emerald-500/5 blur-[100px]"></div>
        </div>

        {{-- Coluna 1: Fila --}}
        <aside class="relative z-10 flex w-full shrink-0 flex-col border-b border-white/10 bg-white/[0.02] backdrop-blur-3xl lg:w-85 lg:border-b-0 lg:border-r">
            <div class="p-6 border-b border-white/5">
                <div class="mb-4 flex items-start justify-between gap-2">
                    <div>
                        <h2 class="flex items-center gap-2 text-sm font-black uppercase italic tracking-widest text-white leading-none">
                            <x-heroicon-o-queue-list class="h-4 w-4 text-primary-400" />
                            Fluxo Ativo
                        </h2>
                        <p class="mt-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest italic">Monitoramento em tempo real</p>
                    </div>
                    <span
                        class="shrink-0 rounded-full border border-primary-500/20 bg-primary-500/10 px-3 py-1 text-[10px] font-black tabular-nums text-primary-400"
                    >
                        {{ $this->conversations->total() }}
                    </span>
                </div>
                <div class="relative group">
                    <x-heroicon-o-magnifying-glass
                        class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-500 transition-colors group-focus-within:text-primary-400"
                    />
                    <input
                        type="search"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Buscar por nome ou ID…"
                        autocomplete="off"
                        class="w-full rounded-2xl border border-white/5 bg-white/5 py-3 pl-11 pr-4 text-xs font-medium text-white placeholder:text-slate-600 focus:border-primary-500/30 focus:outline-none focus:ring-4 focus:ring-primary-500/10 transition-all"
                    />
                </div>
            </div>

            <div class="custom-scrollbar flex-1 overflow-y-auto py-2">
                @forelse($this->conversations as $conversa)
                    @php $st = $convStatus($conversa->status); @endphp
                    <button
                        type="button"
                        wire:click="selectConversation({{ $conversa->id }})"
                        class="group relative flex w-full p-4 text-left transition-all hover:bg-white/[0.03] focus:outline-none {{ $activeConversationId === $conversa->id ? 'bg-white/[0.05]' : '' }}"
                    >
                        <span
                            class="absolute right-0 top-1/2 -translate-y-1/2 h-8 w-1 rounded-l-full bg-primary-500 transition-all duration-500 {{ $activeConversationId === $conversa->id ? 'opacity-100 scale-y-100' : 'opacity-0 scale-y-0 group-hover:opacity-100 group-hover:scale-y-50' }}"
                        ></span>
                        
                        <div class="flex w-full items-start gap-4">
                            <div class="relative shrink-0">
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-2xl border border-white/10 bg-primary-600/20 text-sm font-black text-white italic shadow-lg shadow-primary-600/10"
                                >
                                    {{ $contactInitials($conversa->client_name) }}
                                </div>
                                @if(($conversa->status ?? '') === 'open')
                                    <span class="absolute -bottom-1 -right-1 h-3.5 w-3.5 rounded-full border-2 border-[#020617] bg-emerald-500 shadow-[0_0_8px_#10b981]"></span>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="truncate text-xs font-black text-white uppercase italic tracking-tight">{{ $conversa->client_name }}</span>
                                    <time
                                        class="shrink-0 text-[9px] font-black text-slate-600 uppercase tracking-widest"
                                    >
                                        {{ $conversa->updated_at->format('H:i') }}
                                    </time>
                                </div>
                                <p class="truncate text-[10px] font-bold text-slate-500 mt-0.5">{{ $conversa->client_phone ?? 'CÓDIGO INTERNO' }}</p>
                                <div class="mt-2 flex flex-wrap items-center gap-2">
                                    <span class="inline-flex rounded-full border px-2 py-0.5 text-[8px] font-black uppercase tracking-widest italic {{ $st['class'] }}">
                                        {{ $st['label'] }}
                                    </span>
                                    @if($conversa->channel?->type)
                                        <span class="text-[8px] font-black text-slate-700 uppercase tracking-[0.2em]">{{ $conversa->channel->type }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </button>
                @empty
                    <div class="px-6 py-12 text-center">
                        <div class="p-4 rounded-3xl bg-white/[0.02] border border-white/5 inline-block mb-4">
                            <x-heroicon-o-inbox class="h-8 w-8 text-slate-700" />
                        </div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-600 italic">Vazio Estrutural</p>
                    </div>
                @endforelse

                @if ($this->conversations->hasPages())
                    <div class="p-4">
                        {{ $this->conversations->links() }}
                    </div>
                @endif
            </div>
        </aside>

        {{-- Coluna 2: Chat --}}
        <section class="relative flex min-h-[320px] min-w-0 flex-1 flex-col bg-white/[0.01] backdrop-blur-sm">
            @if ($this->activeConversation)
                <header
                    class="relative z-20 flex flex-wrap items-center justify-between gap-4 border-b border-white/10 bg-white/[0.03] px-6 py-4 backdrop-blur-3xl shadow-xl"
                >
                    <div class="flex min-w-0 items-center gap-4">
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-white/15 bg-primary-600 shadow-xl shadow-primary-600/20 text-sm font-black text-white italic"
                        >
                            {{ $contactInitials($this->activeConversation->client_name) }}
                        </div>
                        <div class="min-w-0">
                            <h3 class="truncate text-sm font-black text-white uppercase italic tracking-tight">{{ $this->activeConversation->client_name }}</h3>
                            <div class="mt-1 flex flex-wrap items-center gap-3">
                                <span class="flex items-center gap-1.5 text-[9px] font-black uppercase tracking-widest text-emerald-400">
                                    <span class="relative flex h-1.5 w-1.5">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-500"></span>
                                    </span>
                                    {{ $this->activeConversation->channel?->type ?? 'Canal Ativo' }}
                                </span>
                                @php $hst = $convStatus($this->activeConversation->status); @endphp
                                <span class="rounded-full border px-2 py-0.5 text-[8px] font-black uppercase tracking-widest italic {{ $hst['class'] }}">{{ $hst['label'] }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            class="rounded-xl p-2.5 text-slate-500 transition-all hover:bg-white/10 hover:text-white border border-transparent hover:border-white/10"
                            title="Espiar Fluxo"
                            disabled
                        >
                            <x-heroicon-o-eye class="h-5 w-5" />
                        </button>
                        <button
                            type="button"
                            class="rounded-xl p-2.5 text-slate-500 transition-all hover:bg-white/10 hover:text-white border border-transparent hover:border-white/10"
                            title="Transferir Atendimento"
                            disabled
                        >
                            <x-heroicon-o-arrow-path-rounded-square class="h-5 w-5" />
                        </button>
                        <button
                            type="button"
                            class="rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-5 py-2 text-[10px] font-black uppercase tracking-widest text-emerald-400 transition-all hover:bg-emerald-500/20 active:scale-95 shadow-lg shadow-emerald-500/10 italic"
                            title="Encerrar"
                            disabled
                        >
                            Finalizar
                        </button>
                    </div>
                </header>

                <div
                    class="custom-scrollbar relative z-10 flex flex-1 flex-col overflow-y-auto p-6 sm:p-8 space-y-8"
                    id="chat-messages"
                >
                    <!-- Visual Depth for Messages -->
                    <div class="absolute inset-0 bg-gradient-to-b from-primary-500/[0.03] to-transparent pointer-events-none"></div>

                    @if ($this->activeMessagesHasMore)
                        <div class="flex justify-center pb-4">
                            <button
                                type="button"
                                wire:click="loadMoreMessages"
                                wire:loading.attr="disabled"
                                class="rounded-full border border-white/10 bg-white/5 px-6 py-2 text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 transition-all hover:bg-white/10 hover:text-white italic"
                            >
                                <span wire:loading.remove wire:target="loadMoreMessages">Histórico Anterior</span>
                                <span wire:loading wire:target="loadMoreMessages">Sincronizando…</span>
                            </button>
                        </div>
                    @endif

                    <div class="space-y-6">
                        @forelse($this->activeMessages as $message)
                            @php
                                $isOutbound = in_array($message->sender_type, ['agent', 'bot', 'internal']);
                                $isInternal = $message->sender_type === 'internal';
                            @endphp

                            @if (! $isOutbound)
                                <div class="flex justify-start animate-in fade-in slide-in-from-left-4 duration-500">
                                    <div class="max-w-[85%] lg:max-w-[70%] relative group">
                                        <div class="rounded-3xl rounded-tl-sm border border-white/5 bg-white/[0.04] p-4 shadow-xl backdrop-blur-md">
                                            @if ($message->message_type === 'image')
                                                <img
                                                    src="{{ asset('storage/'.$message->attachment_path) }}"
                                                    class="mb-3 max-h-72 w-full rounded-2xl object-cover border border-white/10"
                                                    alt=""
                                                />
                                            @elseif($message->message_type === 'file')
                                                <a
                                                    href="{{ asset('storage/'.$message->attachment_path) }}"
                                                    target="_blank"
                                                    class="mb-3 flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 p-3 text-xs font-bold text-primary-400 transition-all hover:bg-white/10"
                                                >
                                                    <x-heroicon-o-document-arrow-down class="h-5 w-5" />
                                                    Documento Recebido
                                                </a>
                                            @endif
                                            <p class="text-sm font-medium leading-relaxed text-slate-100">{{ $message->body }}</p>
                                        </div>
                                        <time class="mt-2 block text-left text-[8px] font-black uppercase tracking-widest text-slate-600 italic px-2">{{ $message->created_at->format('H:i') }}</time>
                                    </div>
                                </div>
                            @else
                                <div class="flex justify-end animate-in fade-in slide-in-from-right-4 duration-500">
                                    <div class="max-w-[85%] lg:max-w-[70%] relative group">
                                        <div class="rounded-3xl rounded-tr-sm border shadow-2xl backdrop-blur-md p-4 {{ $isInternal ? 'border-amber-500/30 bg-amber-500/10' : ($message->sender_type === 'bot' ? 'border-indigo-500/30 bg-indigo-600/20' : 'border-primary-500/30 bg-primary-600/20') }}">
                                            @if ($isInternal)
                                                <div class="mb-2 inline-flex items-center gap-1.5 rounded-lg bg-amber-500/20 px-2 py-0.5 text-[8px] font-black uppercase tracking-widest text-amber-400 italic">
                                                    <x-heroicon-o-lock-closed class="h-3 w-3" />
                                                    Sussurro Interno
                                                </div>
                                            @endif

                                            @if ($message->message_type === 'image')
                                                <img
                                                    src="{{ asset('storage/'.$message->attachment_path) }}"
                                                    class="mb-3 max-h-72 w-full rounded-2xl object-cover border border-white/10"
                                                    alt=""
                                                />
                                            @elseif($message->message_type === 'file')
                                                <a
                                                    href="{{ asset('storage/'.$message->attachment_path) }}"
                                                    target="_blank"
                                                    class="mb-3 flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 p-3 text-xs font-bold text-white/80 transition-all hover:bg-white/10"
                                                >
                                                    <x-heroicon-o-document-arrow-down class="h-5 w-5" />
                                                    Arquivo Enviado
                                                </a>
                                            @endif

                                            <p class="text-sm font-medium leading-relaxed text-white">{{ $message->body }}</p>

                                            <div class="mt-3 flex items-center justify-end gap-2 border-t border-white/5 pt-2">
                                                <span class="mr-auto text-[8px] font-black uppercase tracking-widest text-white/30 italic">
                                                    @if ($isInternal)
                                                        Equipa
                                                    @elseif($message->sender_type === 'bot')
                                                        IA Bot
                                                    @else
                                                        Agente
                                                    @endif
                                                </span>
                                                <time class="text-[8px] font-black text-white/50">{{ $message->created_at->format('H:i') }}</time>
                                                @if (! $isInternal)
                                                    <x-heroicon-s-check-badge class="h-3 w-3 text-primary-400" />
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @empty
                            <div class="py-12 text-center">
                                <div class="p-6 rounded-[2rem] bg-white/[0.02] border border-white/5 inline-block mb-4">
                                    <x-heroicon-o-sparkles class="h-10 w-10 text-primary-500/20" />
                                </div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-600 italic">Inicie a conversa agora</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="relative z-20 border-t border-white/10 bg-black/40 p-6 backdrop-blur-3xl shadow-2xl">
                    @if ($attachment)
                        <div
                            class="absolute bottom-full left-6 mb-4 flex items-center gap-3 rounded-2xl border border-primary-500/30 bg-[#020617] p-3 shadow-2xl animate-in fade-in slide-in-from-bottom-2"
                        >
                            <div class="p-1.5 rounded-lg bg-primary-500/20">
                                <x-heroicon-o-paper-clip class="h-4 w-4 text-primary-400" />
                            </div>
                            <span class="max-w-[200px] truncate text-[10px] font-black uppercase tracking-widest text-white italic">{{ $attachment->getClientOriginalName() }}</span>
                            <button
                                type="button"
                                wire:click="$set('attachment', null)"
                                class="text-red-500 hover:text-red-400 transition-colors"
                            >
                                <x-heroicon-o-x-circle class="h-5 w-5" />
                            </button>
                        </div>
                    @endif

                    @if ($showQuickReplies)
                        <div
                            class="absolute bottom-full left-6 right-6 z-50 mb-4 overflow-hidden rounded-3xl border border-primary-500/30 bg-[#020617]/95 shadow-2xl backdrop-blur-3xl animate-in fade-in slide-in-from-bottom-4"
                        >
                            <div class="flex items-center gap-3 border-b border-white/10 bg-white/5 px-5 py-3">
                                <x-heroicon-o-bolt class="h-4 w-4 text-primary-400" />
                                <span class="text-[10px] font-black uppercase tracking-widest text-white italic">Sugestões Rápidas</span>
                                <span class="ml-auto text-[8px] font-bold text-slate-500 uppercase tracking-widest">ESC: Fechar</span>
                            </div>
                            <div class="custom-scrollbar max-h-64 overflow-y-auto">
                                @forelse($this->quickReplies as $reply)
                                    <button
                                        type="button"
                                        wire:click="insertQuickReply(@js($reply->message))"
                                        class="flex w-full flex-col gap-1 border-b border-white/5 px-5 py-4 text-left transition-all hover:bg-white/[0.05]"
                                    >
                                        <span
                                            class="inline-flex w-fit rounded-lg border border-primary-500/20 bg-primary-500/10 px-2 py-0.5 font-mono text-[9px] font-black text-primary-400 tracking-wider"
                                        >
                                            /{{ $reply->shortcut }}
                                        </span>
                                        <span class="truncate text-xs font-medium text-slate-400 mt-1">{{ $reply->message }}</span>
                                    </button>
                                @empty
                                    <p class="px-5 py-8 text-center text-[10px] font-black uppercase tracking-widest text-slate-700 italic">
                                        Nenhum atalho para “{{ $quickReplySearch }}”
                                    </p>
                                @endforelse
                            </div>
                        </div>
                    @endif

                    <div class="mb-4 flex flex-wrap items-center gap-3">
                        <button
                            type="button"
                            wire:click="toggleInternal"
                            class="group relative flex items-center gap-2 rounded-full border px-4 py-2 text-[9px] font-black uppercase tracking-widest italic transition-all {{ $isInternal ? 'border-amber-500/30 bg-amber-500/10 text-amber-400 shadow-lg shadow-amber-500/10' : 'border-white/5 bg-white/5 text-slate-500 hover:bg-white/10 hover:text-white' }}"
                        >
                            <span class="relative flex h-1.5 w-1.5">
                                <span class="{{ $isInternal ? 'animate-ping' : '' }} absolute inline-flex h-full w-full rounded-full {{ $isInternal ? 'bg-amber-400 opacity-75' : 'bg-slate-600' }}"></span>
                                <span class="relative inline-flex rounded-full h-1.5 w-1.5 {{ $isInternal ? 'bg-amber-500' : 'bg-slate-700' }}"></span>
                            </span>
                            {{ $isInternal ? 'Sussurro Equipa' : 'Conversa Pública' }}
                        </button>
                    </div>

                    <form
                        wire:submit.prevent="sendMessage"
                        class="flex items-end gap-3 rounded-2xl border bg-white/[0.03] p-3 transition-all focus-within:bg-white/[0.05] shadow-inner {{ $isInternal ? 'border-amber-500/20 focus-within:border-amber-500/40' : 'border-white/5 focus-within:border-primary-500/30' }}"
                    >
                        <button
                            type="button"
                            @click="$refs.fileInput.click()"
                            class="rounded-xl p-3 text-slate-500 transition-all hover:bg-white/10 hover:text-primary-400 {{ $attachment ? 'bg-primary-500/10 text-primary-400' : '' }}"
                        >
                            <x-heroicon-o-paper-clip class="h-6 w-6" />
                        </button>
                        <input type="file" x-ref="fileInput" wire:model="attachment" class="hidden" />

                        <textarea
                            wire:model.live="newMessage"
                            wire:keydown.enter.prevent="sendMessage"
                            placeholder="{{ $isInternal ? 'Digite uma nota interna...' : 'Escreva sua mensagem ou digite / para atalhos...' }}"
                            class="max-h-48 min-h-[3rem] flex-1 resize-none border-0 bg-transparent py-3 text-sm font-medium text-white placeholder:text-slate-600 focus:ring-0"
                            rows="1"
                        ></textarea>

                        <button
                            type="submit"
                            class="rounded-xl p-3 shadow-2xl transition-all active:scale-90 disabled:opacity-50 {{ $isInternal ? 'bg-amber-600 hover:bg-amber-500 shadow-amber-600/20' : 'bg-primary-600 hover:bg-primary-500 shadow-primary-600/20' }} text-white"
                        >
                            <x-heroicon-s-paper-airplane class="h-6 w-6" />
                        </button>
                    </form>
                    
                    <div class="mt-4 flex justify-between items-center px-2">
                        <p class="text-[8px] font-black uppercase tracking-[0.2em] text-slate-700 italic">Zynkora Operational Layer v3.0</p>
                        <span class="text-[8px] font-bold text-slate-600 uppercase tracking-widest italic">Criptografia Ativa</span>
                    </div>
                </div>
            @else
                <div class="flex flex-1 flex-col items-center justify-center gap-6 bg-gradient-to-b from-transparent to-primary-950/10 p-12 text-center">
                    <div class="relative">
                        <div class="absolute inset-0 bg-primary-500/20 blur-3xl rounded-full"></div>
                        <x-heroicon-o-chat-bubble-left-right class="relative h-24 w-24 text-primary-500/20" />
                    </div>
                    <div class="space-y-2">
                        <h2 class="text-xl font-black text-white uppercase italic tracking-tight">Seleção de Terminal</h2>
                        <p class="max-w-xs text-xs font-medium text-slate-500 leading-relaxed mx-auto">
                            Selecione um terminal de atendimento ativo na lista lateral para estabelecer conexão segura e gerenciar mensagens.
                        </p>
                    </div>
                </div>
            @endif
        </section>

        {{-- Coluna 3: Detalhes --}}
        <aside
            class="hidden w-full shrink-0 flex-col border-t border-white/10 bg-white/[0.02] backdrop-blur-3xl lg:flex lg:w-72 lg:border-l lg:border-t-0 xl:w-85"
        >
            @if ($this->activeConversation)
                <div class="p-8 text-center border-b border-white/5 relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-b from-primary-500/5 to-transparent pointer-events-none"></div>
                    
                    <p class="mb-6 text-[9px] font-black uppercase tracking-[0.3em] text-slate-500 italic">Perfil do Contacto</p>
                    
                    <div class="relative inline-block mb-6">
                        <div class="absolute inset-0 bg-primary-500/20 blur-2xl rounded-full"></div>
                        <div
                            class="relative flex h-24 w-24 items-center justify-center rounded-[2rem] border border-white/10 bg-primary-600 shadow-2xl text-3xl font-black text-white italic"
                        >
                            {{ $contactInitials($this->activeConversation->client_name) }}
                        </div>
                    </div>
                    
                    <h2 class="text-xl font-black text-white uppercase italic tracking-tight mb-2">{{ $this->activeConversation->client_name }}</h2>
                    <div class="inline-flex items-center gap-2 rounded-full border border-white/5 bg-white/5 px-4 py-1.5 text-[10px] font-bold text-slate-400">
                        <x-heroicon-o-phone class="h-3.5 w-3.5 text-primary-400" />
                        <span>{{ $this->activeConversation->client_phone ?? 'CÓDIGO OCULTO' }}</span>
                    </div>
                </div>

                <div class="custom-scrollbar flex-1 space-y-8 overflow-y-auto p-6 relative">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between px-2">
                            <h4 class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-600 italic">Categorização</h4>
                            <button type="button" class="text-[9px] font-black uppercase tracking-widest text-primary-400 hover:text-white transition-colors" disabled>
                                EDITAR
                            </button>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <span
                                class="rounded-xl border border-primary-500/20 bg-primary-500/10 px-3 py-1.5 text-[9px] font-black uppercase tracking-widest text-primary-400 italic"
                            >
                                CLIENTE VIP
                            </span>
                            <button
                                class="inline-flex items-center gap-1.5 rounded-xl border border-white/5 bg-white/5 px-3 py-1.5 text-[9px] font-black uppercase tracking-widest text-slate-600 hover:bg-white/10 transition-all cursor-not-allowed"
                            >
                                <x-heroicon-o-plus class="h-3 w-3" />
                                TAG
                            </button>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <h4 class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-600 italic px-2">Estado do Ticket</h4>
                        <div class="overflow-hidden rounded-2xl border border-white/5 bg-white/[0.02]">
                            <div class="flex items-center justify-between px-4 py-4">
                                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Workflow</span>
                                @php $rst = $convStatus($this->activeConversation->status); @endphp
                                <span class="rounded-full border px-3 py-1 text-[8px] font-black uppercase tracking-[0.15em] italic {{ $rst['class'] }}">
                                    {{ $rst['label'] }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <h4 class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-600 italic px-2">
                            Dados de Auditoria
                        </h4>
                        <div class="flex items-center gap-4 rounded-2xl border border-white/5 bg-white/[0.02] p-4 transition-all hover:bg-white/[0.04]">
                            <div class="rounded-xl bg-primary-600/20 p-2.5 text-primary-400 border border-primary-500/20">
                                <x-heroicon-o-ticket class="h-5 w-5" />
                            </div>
                            <div class="min-w-0">
                                <p class="text-[8px] font-black uppercase tracking-[0.15em] text-slate-600 italic">Sincronizado em</p>
                                <p class="text-[11px] font-black text-white italic mt-0.5">
                                    {{ $this->activeConversation->started_at?->format('d/m/Y H:i') ?? 'Sincronizando...' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="flex flex-1 flex-col items-center justify-center p-8 text-center">
                    <x-heroicon-o-user-circle class="mb-4 h-16 w-16 text-slate-800" />
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-700 italic">Aguardando Seleção</p>
                </div>
            @endif
        </aside>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(139, 92, 246, 0.3);
        }
        
        /* Message Animations */
        .animate-in {
            animation-duration: 0.5s;
            animation-fill-mode: both;
        }
        
        @keyframes fade-in { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slide-in-from-left { from { transform: translateX(-20px); } to { transform: translateX(0); } }
        @keyframes slide-in-from-right { from { transform: translateX(20px); } to { transform: translateX(0); } }
        @keyframes slide-in-from-bottom { from { transform: translateY(20px); } to { transform: translateY(0); } }
        
        .fade-in { animation-name: fade-in; }
        .slide-in-from-left-4 { animation-name: slide-in-from-left; }
        .slide-in-from-right-4 { animation-name: slide-in-from-right; }
        .slide-in-from-bottom-4 { animation-name: slide-in-from-bottom; }
    </style>
</x-filament-panels::page>
