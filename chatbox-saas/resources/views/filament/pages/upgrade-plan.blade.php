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
                Sua infraestrutura atual: <span class="text-primary-400 border-b border-primary-500/30 pb-0.5">{{ strtoupper($company->plan) }}</span>
            </p>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-10 relative z-10">
            <!-- Basic Plan -->
            <div @class([
                'flex flex-col p-8 rounded-[2.5rem] border backdrop-blur-3xl transition-all duration-500 shadow-2xl group',
                'border-primary-500/50 bg-primary-500/5 ring-4 ring-primary-500/10' => $company->plan === 'basic',
                'border-white/10 bg-white/[0.015] hover:border-white/20 hover:bg-white/[0.03]' => $company->plan !== 'basic'
            ])>
                <h3 class="mb-2 text-xs font-black uppercase tracking-[0.2em] text-slate-500 italic">Básico</h3>
                <p class="text-[11px] font-medium text-slate-400 h-10 italic">Essencial para validação de fluxos iniciais.</p>
                
                <div class="flex justify-center items-baseline my-10">
                    <span class="text-5xl font-black text-white italic tracking-tighter">R$ 97</span>
                    <span class="ml-2 text-[10px] font-black text-slate-600 uppercase tracking-widest">/mês</span>
                </div>

                <ul role="list" class="mb-10 space-y-5 text-left flex-grow">
                    <li class="flex items-center gap-3">
                        <div class="p-1 rounded-lg bg-emerald-500/10 border border-emerald-500/20">
                            <x-heroicon-m-check-circle class="w-4 h-4 text-emerald-500" />
                        </div>
                        <span class="text-xs font-medium text-slate-300">1 Operador Ativo</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <div class="p-1 rounded-lg bg-emerald-500/10 border border-emerald-500/20">
                            <x-heroicon-m-check-circle class="w-4 h-4 text-emerald-500" />
                        </div>
                        <span class="text-xs font-medium text-slate-300">1 Canal (WPP/Web)</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <div class="p-1 rounded-lg bg-emerald-500/10 border border-emerald-500/20">
                            <x-heroicon-m-check-circle class="w-4 h-4 text-emerald-500" />
                        </div>
                        <span class="text-xs font-medium text-slate-300">Automação Standard</span>
                    </li>
                </ul>
                
                @if($company->plan === 'basic')
                    <button disabled class="w-full py-4 rounded-2xl bg-white/5 border border-white/10 text-slate-600 text-[10px] font-black uppercase tracking-widest italic cursor-not-allowed">Plano Ativo</button>
                @else
                    <x-filament::button wire:click="changePlan('basic')" color="gray" class="w-full fi-btn-color-gray uppercase italic font-black text-[10px] tracking-widest py-4">Mudar Nível</x-filament::button>
                @endif
            </div>

            <!-- Pro Plan -->
            <div @class([
                'flex flex-col p-8 rounded-[2.5rem] border backdrop-blur-3xl transition-all duration-500 shadow-2xl relative overflow-hidden group scale-105',
                'border-primary-500 bg-primary-500/5 ring-8 ring-primary-500/5' => $company->plan === 'pro',
                'border-primary-500/30 bg-[#020617] border-white/20' => $company->plan !== 'pro'
            ])>
                <div class="absolute top-0 right-0 bg-primary-600 text-white px-4 py-1.5 text-[8px] font-black italic rounded-bl-2xl uppercase tracking-[0.2em] border-l border-b border-white/20 shadow-xl">TIER ELITE</div>
                
                <h3 class="mb-2 text-xs font-black uppercase tracking-[0.2em] text-primary-400 italic">Profissional</h3>
                <p class="text-[11px] font-medium text-slate-400 h-10 italic">Escalabilidade para alta demanda operacional.</p>
                
                <div class="flex justify-center items-baseline my-10">
                    <span class="text-5xl font-black text-white italic tracking-tighter">R$ 297</span>
                    <span class="ml-2 text-[10px] font-black text-slate-600 uppercase tracking-widest">/mês</span>
                </div>

                <ul role="list" class="mb-10 space-y-5 text-left flex-grow">
                    <li class="flex items-center gap-3">
                        <div class="p-1 rounded-lg bg-emerald-500/10 border border-emerald-500/20 shadow-[0_0_10px_rgba(16,185,129,0.2)]">
                            <x-heroicon-m-check-circle class="w-4 h-4 text-emerald-400" />
                        </div>
                        <span class="text-xs font-black text-white italic uppercase tracking-tight">5 Operadores Síncronos</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <div class="p-1 rounded-lg bg-emerald-500/10 border border-emerald-500/20">
                            <x-heroicon-m-check-circle class="w-4 h-4 text-emerald-400" />
                        </div>
                        <span class="text-xs font-black text-white italic uppercase tracking-tight">3 Canais Simultâneos</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <div class="p-1 rounded-lg bg-primary-500/20 border border-primary-500/30 animate-pulse">
                            <x-heroicon-m-sparkles class="w-4 h-4 text-primary-400" />
                        </div>
                        <span class="text-xs font-black text-primary-400 italic uppercase tracking-widest">IA Generativa (GPT-4)</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <div class="p-1 rounded-lg bg-emerald-500/10 border border-emerald-500/20">
                            <x-heroicon-m-check-circle class="w-4 h-4 text-emerald-400" />
                        </div>
                        <span class="text-xs font-medium text-slate-300">Analytics de Alta Performance</span>
                    </li>
                </ul>

                @if($company->plan === 'pro')
                    <button disabled class="w-full py-5 rounded-2xl bg-primary-600/20 border border-primary-500/30 text-primary-400 text-[10px] font-black uppercase tracking-widest italic cursor-not-allowed">Contrato Ativo</button>
                @else
                    <x-filament::button wire:click="changePlan('pro')" size="lg" class="w-full fi-btn-color-primary uppercase italic font-black text-[10px] tracking-widest py-5 shadow-xl shadow-primary-600/30">Upgrade para Pro</x-filament::button>
                @endif
            </div>

            <!-- Enterprise Plan -->
            <div @class([
                'flex flex-col p-8 rounded-[2.5rem] border backdrop-blur-3xl transition-all duration-500 shadow-2xl group',
                'border-primary-500/50 bg-primary-500/5 ring-4 ring-primary-500/10' => $company->plan === 'enterprise',
                'border-white/10 bg-white/[0.015] hover:border-white/20 hover:bg-white/[0.03]' => $company->plan !== 'enterprise'
            ])>
                <h3 class="mb-2 text-xs font-black uppercase tracking-[0.2em] text-slate-500 italic">Empresarial</h3>
                <p class="text-[11px] font-medium text-slate-400 h-10 italic">Arquitetura customizada para grandes volumes.</p>
                
                <div class="flex justify-center items-baseline my-10">
                    <span class="text-3xl font-black text-white italic tracking-tighter uppercase">Sob Consulta</span>
                </div>

                <ul role="list" class="mb-10 space-y-5 text-left flex-grow">
                    <li class="flex items-center gap-3">
                        <div class="p-1 rounded-lg bg-emerald-500/10 border border-emerald-500/20">
                            <x-heroicon-m-check-circle class="w-4 h-4 text-emerald-500" />
                        </div>
                        <span class="text-xs font-medium text-slate-300">Nós de Atendimento Ilimitados</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <div class="p-1 rounded-lg bg-emerald-500/10 border border-emerald-500/20">
                            <x-heroicon-m-check-circle class="w-4 h-4 text-emerald-500" />
                        </div>
                        <span class="text-xs font-medium text-slate-300">Integração Omni-Canal Total</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <div class="p-1 rounded-lg bg-amber-500/10 border border-amber-500/20">
                            <x-heroicon-m-paint-brush class="w-4 h-4 text-amber-500" />
                        </div>
                        <span class="text-xs font-black text-amber-500 italic uppercase tracking-widest">White Label Master</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <div class="p-1 rounded-lg bg-emerald-500/10 border border-emerald-500/20">
                            <x-heroicon-m-check-circle class="w-4 h-4 text-emerald-500" />
                        </div>
                        <span class="text-xs font-medium text-slate-300">Suporte Platinum 24/7</span>
                    </li>
                </ul>

                 @if($company->plan === 'enterprise')
                    <button disabled class="w-full py-4 rounded-2xl bg-white/5 border border-white/10 text-slate-600 text-[10px] font-black uppercase tracking-widest italic cursor-not-allowed">Plano Ativo</button>
                @else
                    <x-filament::button wire:click="changePlan('enterprise')" color="warning" class="w-full fi-btn-color-warning uppercase italic font-black text-[10px] tracking-widest py-4">Falar com Consultor</x-filament::button>
                @endif
            </div>
        </div>
    </div>
</x-filament-panels::page>
