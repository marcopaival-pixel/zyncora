@php
    use App\Filament\Resources\DealResource;
    use App\Filament\Resources\PipelineResource;
@endphp

<x-filament-panels::page>
    @if($this->pipelines->isEmpty())
        <div class="flex flex-col items-center justify-center rounded-[3rem] border border-white/10 bg-white/[0.015] backdrop-blur-2xl px-12 py-24 text-center shadow-2xl relative overflow-hidden group">
            <div class="absolute inset-0 bg-gradient-to-br from-primary-500/5 to-transparent pointer-events-none"></div>
            
            <div class="p-6 rounded-[2rem] bg-primary-500/10 border border-primary-500/20 mb-8 relative z-10">
                <x-heroicon-o-funnel class="h-16 w-16 text-primary-400" />
            </div>
            
            <h2 class="relative z-10 text-3xl font-black text-white uppercase italic tracking-tighter">Inicie sua jornada CRM</h2>
            <p class="relative z-10 mt-4 max-w-md text-sm font-medium text-slate-400 leading-relaxed mx-auto">
                Configure seu pipeline visual agora para transformar conversas em oportunidades de receita. Organize seu fluxo de vendas com precisão técnica.
            </p>
            
            <x-filament::button 
                class="mt-10 fi-btn-color-primary" 
                tag="a" 
                icon="heroicon-o-plus" 
                href="{{ PipelineResource::getUrl('create') }}"
            >
                Configurar Funil
            </x-filament::button>
        </div>
    @else
        <div class="space-y-10">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex flex-wrap items-center gap-4">
                    <div class="relative group">
                        <select
                            id="crm-pipeline-select"
                            wire:model.live="selectedPipelineId"
                            class="min-w-[15rem] rounded-2xl border-white/10 bg-white/5 text-xs font-black uppercase italic tracking-widest text-white shadow-xl backdrop-blur-3xl transition-all focus:border-primary-500/50 focus:ring-4 focus:ring-primary-500/10 pr-10"
                        >
                            @foreach($this->pipelines as $pipeline)
                                <option value="{{ $pipeline->id }}" class="bg-[#020617]">{{ $pipeline->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    @if($selectedPipelineId)
                        <a 
                            href="{{ PipelineResource::getUrl('edit', ['record' => $selectedPipelineId]) }}"
                            class="p-2.5 rounded-xl border border-white/5 bg-white/5 text-slate-500 transition-all hover:bg-white/10 hover:text-white"
                            title="Editar Funil"
                        >
                            <x-heroicon-m-pencil-square class="h-5 w-5" />
                        </a>
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-4">
                    <div class="flex items-center gap-3 px-5 py-3 rounded-2xl border border-white/5 bg-white/[0.03] backdrop-blur-3xl">
                        <span class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-500">Pipeline</span>
                        <span class="text-sm font-black text-white italic">{{ $this->stages->sum(fn ($s) => $s->deals->count()) }} Ativos</span>
                    </div>
                    <div class="flex items-center gap-3 px-5 py-3 rounded-2xl border border-white/5 bg-white/[0.03] backdrop-blur-3xl">
                        <span class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-500">Volume Bruto</span>
                        <span class="text-sm font-black text-emerald-400 italic">{{ $this->totalPipelineValue }}</span>
                    </div>
                    
                    <x-filament::button 
                        icon="heroicon-m-plus" 
                        class="fi-btn-color-primary"
                        tag="a"
                        href="{{ DealResource::getUrl('create').($selectedPipelineId ? '?pipeline='.$selectedPipelineId : '') }}"
                    >
                        Novo Negócio
                    </x-filament::button>
                </div>
            </div>

            @if($this->stages->isEmpty())
                <div class="rounded-3xl border border-amber-500/20 bg-amber-500/5 p-6 backdrop-blur-2xl">
                    <div class="flex items-center gap-4">
                        <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-amber-500" />
                        <p class="text-sm font-bold text-amber-200 italic uppercase tracking-tight">
                            Etapa Crítica: Pipeline sem fases definidas. 
                            <a href="{{ PipelineResource::getUrl('edit', ['record' => $selectedPipelineId]) }}" class="underline hover:text-white ml-2">Adicionar Estágios &rarr;</a>
                        </p>
                    </div>
                </div>
            @else
                <div
                    wire:key="kanban-board-{{ $selectedPipelineId }}"
                    class="flex gap-8 overflow-x-auto pb-12 -mx-4 px-4 scrollbar-hide select-none"
                    style="min-height: 70vh;"
                >
                    @foreach($this->stages as $stage)
                        <div class="flex w-85 shrink-0 flex-col">
                            <div class="mb-6 flex items-center justify-between px-2">
                                <div class="flex items-center gap-3">
                                    <div class="h-3 w-3 rounded-full bg-primary-500 shadow-[0_0_10px_#8b5cf6]"></div>
                                    <h3 class="text-xs font-black uppercase tracking-[0.15em] text-white italic">{{ $stage->name }}</h3>
                                    <span class="rounded-lg bg-white/5 border border-white/5 px-2 py-0.5 text-[9px] font-black text-slate-500 italic">
                                        {{ $stage->deals->count() }}
                                    </span>
                                </div>
                            </div>

                            <div
                                data-stage-id="{{ $stage->id }}"
                                class="kanban-column flex-1 space-y-4 rounded-[2rem] border border-white/5 bg-white/[0.015] backdrop-blur-xl p-4 transition-all duration-500 hover:bg-white/[0.03] hover:border-white/10"
                                style="min-height: 400px;"
                            >
                                @foreach($stage->deals as $deal)
                                    <div
                                        data-deal-id="{{ $deal->id }}"
                                        class="kanban-card group/card relative overflow-hidden rounded-[1.5rem] border border-white/5 bg-white/[0.03] shadow-xl transition-all duration-300 hover:scale-[1.02] hover:border-primary-500/30 hover:bg-white/[0.05] cursor-grab active:cursor-grabbing"
                                    >
                                        <div class="absolute left-0 top-0 h-full w-1 bg-primary-500 opacity-0 transition-all duration-500 group-hover/card:opacity-100"></div>
                                        
                                        <div class="p-6 space-y-4">
                                            <h4 class="text-sm font-black text-white leading-tight uppercase italic tracking-tight">
                                                {{ $deal->title }}
                                            </h4>

                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center">
                                                    <x-heroicon-m-user class="h-4 w-4 text-slate-400" />
                                                </div>
                                                <span class="text-[10px] font-bold text-slate-400 truncate tracking-tight">{{ $deal->contact?->name ?? 'Sem Identificação' }}</span>
                                            </div>

                                            <div class="flex items-center justify-between pt-4 border-t border-white/5">
                                                <div class="text-sm font-black text-emerald-400 italic">
                                                    R$ {{ number_format((float) $deal->value, 2, ',', '.') }}
                                                </div>
                                                <a
                                                    href="{{ DealResource::getUrl('edit', ['record' => $deal]) }}"
                                                    class="text-[9px] font-black uppercase tracking-[0.2em] text-primary-400 hover:text-white transition-colors"
                                                    onclick="event.stopPropagation()"
                                                >
                                                    Ficha
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                @if($stage->deals->isEmpty())
                                    <div class="flex h-40 flex-col items-center justify-center opacity-20 group">
                                        <x-heroicon-o-inbox class="mb-2 h-10 w-10 text-slate-400 transition-all group-hover:scale-110" />
                                        <span class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-500 italic">Espaço Disponível</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    @script
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        function initKanbanSortable() {
            if (typeof Sortable === 'undefined') return;
            document.querySelectorAll('.kanban-column').forEach((column) => {
                if (column._sortable) {
                    column._sortable.destroy();
                    column._sortable = null;
                }
                column._sortable = new Sortable(column, {
                    group: 'kanban',
                    animation: 300,
                    ghostClass: 'opacity-20',
                    dragClass: 'opacity-95',
                    easing: 'cubic-bezier(0.23, 1, 0.32, 1)',
                    onEnd: function (evt) {
                        const dealId = evt.item.getAttribute('data-deal-id');
                        const newStageId = evt.to.getAttribute('data-stage-id');
                        if (evt.from !== evt.to && dealId && newStageId) {
                            $wire.updateDealStage(dealId, newStageId);
                        }
                    },
                });
            });
        }

        document.addEventListener('DOMContentLoaded', initKanbanSortable);
        document.addEventListener('livewire:init', () => {
            Livewire.hook('commit', ({ succeed }) => {
                succeed(() => {
                    queueMicrotask(() => initKanbanSortable());
                });
            });
            initKanbanSortable();
        });
    </script>
    @endscript

    <style>
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
        
        .kanban-column {
            background-image: radial-gradient(circle at 2px 2px, rgba(255,255,255,0.02) 1px, transparent 0);
            background-size: 24px 24px;
        }
    </style>
</x-filament-panels::page>
