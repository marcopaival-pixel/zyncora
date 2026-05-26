@php
    $pipeline = max(0, $this->open + $this->waiting);
    $openShare = $this->openShareOfPipeline;
    $waitShare = $pipeline > 0 ? round(100 - $openShare, 1) : 0;
@endphp

<x-filament-panels::page>
    <div class="space-y-10">
        {{-- Cabeçalho / contexto do período --}}
        <div
            class="relative overflow-hidden rounded-[2.5rem] border border-white/10 bg-white/[0.015] backdrop-blur-2xl p-8 shadow-2xl transition-all duration-700 group"
        >
            <div class="absolute inset-0 bg-gradient-to-br from-primary-500/5 to-transparent pointer-events-none"></div>
            <div
                class="pointer-events-none absolute -right-20 -top-20 h-64 w-64 rounded-full bg-primary-500/10 blur-[100px] transition-all duration-700 group-hover:bg-primary-500/20"
            ></div>
            <div
                class="pointer-events-none absolute -bottom-24 -left-16 h-64 w-64 rounded-full bg-indigo-500/5 blur-[100px] transition-all duration-700 group-hover:bg-indigo-500/10"
            ></div>

            <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="space-y-4">
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="inline-flex items-center gap-2 rounded-full border border-primary-500/20 bg-primary-500/10 px-3 py-1 text-[10px] font-black uppercase tracking-[0.2em] text-primary-400">
                            Analytics Hub
                        </div>
                        <span
                            class="inline-flex items-center rounded-full border border-white/10 bg-white/5 px-3 py-1 text-[9px] font-black uppercase tracking-widest text-slate-400 italic"
                        >
                            {{ $this->periodLabel }}
                        </span>
                    </div>
                    <h2 class="text-3xl font-black tracking-tighter text-white uppercase italic md:text-4xl leading-tight">
                        Performance da Operação
                    </h2>
                    <p class="max-w-2xl text-sm font-medium text-slate-400 leading-relaxed">
                        Visão consolidada do volume de conversas e fluxos de atendimento. Inteligência de dados para escalabilidade e eficiência operacional.
                    </p>
                </div>
                <div
                    class="flex shrink-0 items-center gap-3 rounded-2xl border border-white/5 bg-white/[0.03] px-5 py-4 backdrop-blur-3xl"
                >
                    <div class="flex items-center gap-3 text-[10px] font-black uppercase tracking-widest text-slate-500 italic">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        Live Data Streaming
                    </div>
                </div>
            </div>
        </div>

        {{-- KPIs --}}
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-4">
            {{-- Stat Item: Abertas --}}
            <div class="group relative overflow-hidden rounded-[2rem] border border-white/10 bg-white/[0.015] p-6 shadow-xl transition-all duration-500 hover:scale-[1.02] hover:border-blue-500/30 hover:bg-white/[0.03]">
                <div class="absolute -right-8 -top-8 h-32 w-32 rounded-full bg-blue-500/5 blur-[50px] transition-all group-hover:bg-blue-500/15"></div>
                <div class="relative z-10 space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-500">Fluxo Ativo</span>
                        <div class="p-2 rounded-xl bg-blue-500/10 border border-blue-500/20">
                            <x-heroicon-o-chat-bubble-bottom-center-text class="h-4 w-4 text-blue-400" />
                        </div>
                    </div>
                    <p class="text-4xl font-black tracking-tighter text-white italic leading-none">
                        {{ $this->open }}
                    </p>
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest italic">Conversas Abertas</p>
                </div>
            </div>

            {{-- Stat Item: Espera --}}
            <div class="group relative overflow-hidden rounded-[2rem] border border-white/10 bg-white/[0.015] p-6 shadow-xl transition-all duration-500 hover:scale-[1.02] hover:border-amber-500/30 hover:bg-white/[0.03]">
                <div class="absolute -right-8 -top-8 h-32 w-32 rounded-full bg-amber-500/5 blur-[50px] transition-all group-hover:bg-amber-500/15"></div>
                <div class="relative z-10 space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-500">Pendente</span>
                        <div class="p-2 rounded-xl bg-amber-500/10 border border-amber-500/20">
                            <x-heroicon-o-clock class="h-4 w-4 text-amber-400" />
                        </div>
                    </div>
                    <p class="text-4xl font-black tracking-tighter text-white italic leading-none">
                        {{ $this->waiting }}
                    </p>
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest italic">Fila de Espera</p>
                </div>
            </div>

            {{-- Stat Item: Hoje --}}
            <div class="group relative overflow-hidden rounded-[2rem] border border-white/10 bg-white/[0.015] p-6 shadow-xl transition-all duration-500 hover:scale-[1.02] hover:border-violet-500/30 hover:bg-white/[0.03]">
                <div class="absolute -right-8 -top-8 h-32 w-32 rounded-full bg-violet-500/5 blur-[50px] transition-all group-hover:bg-violet-500/15"></div>
                <div class="relative z-10 space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-500">Output Hoje</span>
                        <div class="p-2 rounded-xl bg-violet-500/10 border border-violet-500/20">
                            <x-heroicon-o-sun class="h-4 w-4 text-violet-400" />
                        </div>
                    </div>
                    <p class="text-4xl font-black tracking-tighter text-white italic leading-none">
                        {{ $this->closedToday }}
                    </p>
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest italic">Encerradas</p>
                </div>
            </div>

            {{-- Stat Item: Mês --}}
            <div class="group relative overflow-hidden rounded-[2rem] border border-white/10 bg-white/[0.015] p-6 shadow-xl transition-all duration-500 hover:scale-[1.02] hover:border-emerald-500/30 hover:bg-white/[0.03]">
                <div class="absolute -right-8 -top-8 h-32 w-32 rounded-full bg-emerald-500/5 blur-[50px] transition-all group-hover:bg-emerald-500/15"></div>
                <div class="relative z-10 space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-500">Total Mensal</span>
                        <div class="p-2 rounded-xl bg-emerald-500/10 border border-emerald-500/20">
                            <x-heroicon-o-check-circle class="h-4 w-4 text-emerald-400" />
                        </div>
                    </div>
                    <p class="text-4xl font-black tracking-tighter text-white italic leading-none">
                        {{ $this->closedMonth }}
                    </p>
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest italic">Sessões Concluídas</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-5">
            {{-- Distribuição da fila --}}
            <div class="lg:col-span-3 rounded-[2rem] border border-white/5 bg-white/[0.01] p-8 backdrop-blur-2xl transition-all hover:bg-white/[0.02]">
                <div class="mb-6 flex items-center gap-3">
                    <div class="h-3 w-3 rounded-full bg-primary-500 shadow-[0_0_10px_#8b5cf6]"></div>
                    <h3 class="text-xs font-black uppercase tracking-[0.15em] text-white italic">Distribuição de Fluxo Ativo</h3>
                </div>
                
                <p class="mb-8 text-xs font-medium text-slate-400 leading-relaxed">
                    Proporção técnica entre sessões em curso e tickets em fila de processamento (Total: {{ $pipeline }}).
                </p>

                @if ($pipeline > 0)
                    <div class="space-y-6">
                        <div
                            class="flex h-3 overflow-hidden rounded-full bg-white/5 border border-white/5"
                            role="img"
                        >
                            <div
                                class="bg-gradient-to-r from-blue-500 to-blue-400 transition-all duration-1000 shadow-[0_0_15px_rgba(59,130,246,0.3)]"
                                style="width: {{ $openShare }}%"
                                title="Abertas: {{ $openShare }}%"
                            ></div>
                            <div
                                class="bg-gradient-to-r from-amber-500 to-amber-400 transition-all duration-1000 shadow-[0_0_15px_rgba(245,158,11,0.3)]"
                                style="width: {{ $waitShare }}%"
                                title="Em espera: {{ $waitShare }}%"
                            ></div>
                        </div>
                        <div class="flex flex-wrap gap-6">
                            <span class="inline-flex items-center gap-3 text-[10px] font-black uppercase tracking-widest text-slate-400 italic">
                                <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                                Abertas ({{ $openShare }}%)
                            </span>
                            <span class="inline-flex items-center gap-3 text-[10px] font-black uppercase tracking-widest text-slate-400 italic">
                                <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                                Em espera ({{ $waitShare }}%)
                            </span>
                        </div>
                    </div>
                @else
                    <div class="py-12 text-center opacity-30">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-500 italic">Aguardando dados estruturais...</p>
                    </div>
                @endif
            </div>

            {{-- Indicadores rápidos --}}
            <div class="lg:col-span-2 rounded-[2rem] border border-white/5 bg-white/[0.01] p-8 backdrop-blur-2xl transition-all hover:bg-white/[0.02]">
                <div class="mb-6 flex items-center gap-3">
                    <div class="h-3 w-3 rounded-full bg-emerald-500 shadow-[0_0_10px_#10b981]"></div>
                    <h3 class="text-xs font-black uppercase tracking-[0.15em] text-white italic">Leitura Estratégica</h3>
                </div>

                <ul class="space-y-6">
                    <li class="flex gap-4 group">
                        <div class="p-2 rounded-xl bg-primary-500/10 border border-primary-500/20 group-hover:bg-primary-500/20 transition-all">
                            <x-heroicon-o-arrow-trending-up class="h-4 w-4 text-primary-400" />
                        </div>
                        <p class="text-[11px] font-medium text-slate-400 leading-relaxed">
                            Compare o volume <strong class="text-white italic uppercase tracking-tighter">Encerrado</strong> com a média histórica para identificar gargalos.
                        </p>
                    </li>
                    <li class="flex gap-4 group">
                        <div class="p-2 rounded-xl bg-amber-500/10 border border-amber-500/20 group-hover:bg-amber-500/20 transition-all">
                            <x-heroicon-o-queue-list class="h-4 w-4 text-amber-400" />
                        </div>
                        <p class="text-[11px] font-medium text-slate-400 leading-relaxed">
                            Picos em <strong class="text-white italic uppercase tracking-tighter">Fila</strong> exigem ativação imediata de automações de transbordo.
                        </p>
                    </li>
                    <li class="flex gap-4 group">
                        <div class="p-2 rounded-xl bg-emerald-500/10 border border-emerald-500/20 group-hover:bg-emerald-500/20 transition-all">
                            <x-heroicon-o-globe-alt class="h-4 w-4 text-emerald-400" />
                        </div>
                        <p class="text-[11px] font-medium text-slate-400 leading-relaxed">
                            Dados agregados de todos os canais integrados (WhatsApp, Webchat, Telegram).
                        </p>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Rodapé: dica + exportação --}}
        <div
            class="flex flex-col gap-6 rounded-[2rem] border border-white/5 bg-white/[0.01] p-6 backdrop-blur-3xl lg:flex-row lg:items-center lg:justify-between"
        >
            <div class="flex items-center gap-4">
                <div class="p-3 rounded-2xl bg-white/5 border border-white/10">
                    <x-heroicon-o-light-bulb class="h-6 w-6 text-primary-400" />
                </div>
                <p class="max-w-2xl text-[10px] font-bold leading-relaxed text-slate-500 uppercase tracking-widest italic">
                    Dica: Exporte relatórios para auditoria técnica e cruze com métricas de SLA (TMA/TMR) para otimização de staffing.
                </p>
            </div>
            
            <x-filament::button
                wire:click="exportCsv"
                wire:loading.attr="disabled"
                wire:target="exportCsv"
                icon="heroicon-o-arrow-down-tray"
                class="fi-btn-color-primary shrink-0"
            >
                <span wire:loading.remove wire:target="exportCsv">Exportar Dataset</span>
                <span wire:loading wire:target="exportCsv">Processando...</span>
            </x-filament::button>
        </div>
    </div>
</x-filament-panels::page>
