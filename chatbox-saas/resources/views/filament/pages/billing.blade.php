<x-filament-panels::page>
    <div class="billing-container py-16 relative">
        <!-- Immersive Background Accents -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden opacity-30">
            <div class="absolute top-[10%] left-[20%] w-full h-full rounded-full bg-primary-500/5 blur-[150px]"></div>
            <div class="absolute bottom-[10%] right-[20%] w-full h-full rounded-full bg-emerald-500/5 blur-[150px]"></div>
        </div>

        <div class="max-w-7xl mx-auto px-6 relative z-10">
            <div class="text-center mb-24">
                <div class="inline-flex items-center gap-2 rounded-full border border-primary-500/20 bg-primary-500/10 px-4 py-1.5 text-[10px] font-black uppercase tracking-[0.3em] text-primary-400 mb-6">
                    Subscription Tiers
                </div>
                <h2 class="text-4xl font-black text-white sm:text-6xl uppercase italic tracking-tighter leading-none mb-6">
                    Expansão de Capacidade
                </h2>
                <p class="mt-4 text-sm font-medium text-slate-400 max-w-2xl mx-auto leading-relaxed italic">
                    Selecione a infraestrutura ideal para escalar sua operação de inteligência e atendimento multicanal.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-10 md:grid-cols-3">
                @foreach($plans as $plan)
                    <div class="relative group h-full">
                        <div class="absolute -inset-1 bg-gradient-to-r {{ $plan->is_popular ? 'from-primary-600 to-emerald-600' : 'from-white/10 to-transparent' }} rounded-[3rem] blur opacity-20 group-hover:opacity-40 transition duration-1000"></div>
                        
                        <div class="relative h-full flex flex-col p-10 rounded-[3rem] border border-white/10 bg-[#020617]/80 backdrop-blur-3xl shadow-2xl transition-all duration-500 group-hover:bg-white/[0.02] group-hover:border-primary-500/30">
                            @if($plan->is_popular)
                                <div class="absolute -top-4 left-1/2 -translate-x-1/2 px-6 py-2 bg-gradient-to-r from-primary-600 to-indigo-600 text-white text-[10px] font-black italic rounded-full uppercase tracking-[0.2em] shadow-xl shadow-primary-600/20 border border-white/20">
                                    Recomendado
                                </div>
                            @endif

                            <div class="mb-10 text-center">
                                <h3 class="text-xs font-black text-slate-500 uppercase tracking-[0.25em] mb-4">{{ $plan->name }}</h3>
                                <div class="flex flex-col items-center">
                                    <div class="flex items-baseline mb-2">
                                        <span class="text-5xl font-black text-white italic tracking-tighter">R$ {{ number_format($plan->price, 0, ',', '.') }}</span>
                                        <span class="ml-2 text-xs font-black text-slate-600 uppercase tracking-widest">/mês</span>
                                    </div>
                                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest italic h-10">{{ $plan->description }}</p>
                                </div>
                            </div>

                            <div class="flex-grow space-y-6 mb-12">
                                @foreach($plan->features as $feature)
                                    <div class="flex items-center gap-4 group/item">
                                        <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-500/10 border border-emerald-500/20">
                                            <x-heroicon-s-check class="h-3.5 w-3.5 text-emerald-400 transition-transform group-hover/item:scale-125" />
                                        </div>
                                        <span class="text-xs font-medium text-slate-300 group-hover/item:text-white transition-colors">{{ $feature }}</span>
                                    </div>
                                @endforeach
                            </div>

                            <button 
                                wire:click="selectPlan({{ $plan->id }})"
                                class="w-full py-5 rounded-2xl font-black text-xs uppercase tracking-[0.2em] italic transition-all duration-300 active:scale-95 shadow-xl {{ $plan->is_popular ? 'bg-primary-600 hover:bg-primary-500 text-white shadow-primary-600/20' : 'bg-white/5 hover:bg-white/10 text-white border border-white/10' }}"
                            >
                                {{ $currentPlanId == $plan->id ? 'Contrato Ativo' : 'Efetivar Adesão' }}
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-6 mt-16 relative z-10">
            <h3 class="text-2xl font-black text-white sm:text-3xl uppercase italic tracking-tighter mb-6">
                Histórico Financeiro
            </h3>
            <div class="bg-white/5 border border-white/10 rounded-2xl p-6 shadow-xl backdrop-blur-md">
                {{ $this->table }}
            </div>
        </div>
    </div>
</x-filament-panels::page>
