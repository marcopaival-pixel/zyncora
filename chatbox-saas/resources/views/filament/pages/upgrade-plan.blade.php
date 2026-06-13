<x-filament-panels::page>
    <div class="upgrade-container py-12 relative">
        <!-- Background Accents -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden opacity-20">
            <div class="absolute top-0 right-0 w-[50%] h-[50%] rounded-full bg-primary-500/10 blur-[120px]"></div>
        </div>

        <header class="text-center mb-16 relative z-10">
            <div class="inline-flex items-center gap-2 rounded-full border border-primary-500/20 bg-primary-500/10 px-4 py-1 text-[9px] font-black uppercase tracking-[0.3em] text-primary-400 mb-6">
                Upgrade Management
            </div>
            <h2 class="text-3xl font-black text-white uppercase italic tracking-tighter leading-none mb-4">Escolha seu novo patamar</h2>
            <p class="text-slate-500 text-xs font-bold uppercase tracking-widest italic">
                Sua infraestrutura atual: <span class="text-primary-400 border-b border-primary-500/30 pb-0.5">{{ strtoupper($company->plan ?? 'N/A') }}</span>
            </p>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-10 relative z-10">
            @foreach($plans as $plan)
                @php
                    $isActive = $company->plan === $plan->slug;
                    $isPopular = $plan->is_popular;
                    $priceParts = explode('.', number_format($plan->price, 2, '.', ''));
                    $reais = $priceParts[0];
                    $centavos = $priceParts[1] ?? '00';
                @endphp
                <div @class([
                    'flex flex-col p-8 rounded-[2.5rem] border backdrop-blur-3xl transition-all duration-500 shadow-2xl relative overflow-hidden group',
                    'border-primary-500 bg-primary-500/10 ring-8 ring-primary-500/20 scale-105 z-20' => $isPopular && !$isActive,
                    'border-white/10 bg-white/[0.015] hover:border-white/20 hover:bg-white/[0.03]' => !$isPopular && !$isActive,
                    'border-primary-500/50 bg-primary-500/5 ring-4 ring-primary-500/10' => $isActive,
                ])>
                    @if($isPopular)
                        <div class="absolute top-0 w-full left-0 bg-primary-600 text-center text-white py-1.5 text-[9px] font-black italic uppercase tracking-widest shadow-xl flex justify-center items-center gap-1">
                            <x-heroicon-s-star class="w-3 h-3 text-yellow-300" />
                            Mais escolhido
                        </div>
                    @endif
                    
                    <h3 class="mt-6 mb-2 text-xs font-black uppercase tracking-[0.2em] {{ $isPopular ? 'text-primary-400' : 'text-slate-500' }} italic">{{ $plan->name }}</h3>
                    <p class="text-[11px] font-medium text-slate-400 h-10 italic">{{ $plan->description }}</p>
                    
                    <div class="flex justify-center items-baseline my-8">
                        <span class="text-5xl font-black text-white italic tracking-tighter">R$ {{ $reais }}<span class="text-2xl">,{{ $centavos }}</span></span>
                        <span class="ml-2 text-[10px] font-black text-slate-600 uppercase tracking-widest">/mês</span>
                    </div>

                    <div class="text-center mb-6">
                        <span class="inline-block py-1.5 px-3 {{ $isPopular ? 'bg-primary-500 border border-primary-400 text-white shadow-[0_0_15px_rgba(14,165,233,0.4)]' : 'bg-primary-900/40 border border-primary-500/30 text-primary-400' }} rounded-lg text-xs font-black uppercase tracking-widest italic">
                            {{ number_format($plan->max_ai_conversations, 0, '', '.') }} Conversas IA / mês
                        </span>
                    </div>

                    <ul role="list" class="mb-10 space-y-5 text-left flex-grow">
                        <li class="flex items-center gap-3">
                            <div class="p-1 rounded-lg bg-emerald-500/10 border border-emerald-500/20">
                                <x-heroicon-m-check-circle class="w-4 h-4 text-emerald-500" />
                            </div>
                            <span class="text-xs font-medium text-slate-300">{{ $plan->max_channels }} Canais</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <div class="p-1 rounded-lg bg-emerald-500/10 border border-emerald-500/20">
                                <x-heroicon-m-check-circle class="w-4 h-4 text-emerald-500" />
                            </div>
                            <span class="text-xs font-medium text-slate-300">{{ $plan->max_attendants }} Atendentes</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <div class="p-1 rounded-lg bg-emerald-500/10 border border-emerald-500/20">
                                <x-heroicon-m-check-circle class="w-4 h-4 text-emerald-500" />
                            </div>
                            <span class="text-xs font-medium text-slate-300">{{ $plan->max_chatbots }} Chatbot(s)</span>
                        </li>
                        @if($plan->has_whitelabel)
                        <li class="flex items-center gap-3">
                            <div class="p-1 rounded-lg bg-amber-500/10 border border-amber-500/20">
                                <x-heroicon-m-paint-brush class="w-4 h-4 text-amber-500" />
                            </div>
                            <span class="text-xs font-black text-amber-500 italic uppercase tracking-widest">White Label</span>
                        </li>
                        @endif
                    </ul>
                    
                    @if($isActive)
                        <button disabled class="w-full py-4 rounded-2xl bg-white/5 border border-white/10 text-slate-600 text-[10px] font-black uppercase tracking-widest italic cursor-not-allowed">Plano Ativo</button>
                    @else
                        <x-filament::button wire:click="changePlan('{{ $plan->slug }}')" color="{{ $isPopular ? 'primary' : 'gray' }}" class="w-full uppercase italic font-black text-[10px] tracking-widest py-4">Assinar {{ $plan->name }}</x-filament::button>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</x-filament-panels::page>
