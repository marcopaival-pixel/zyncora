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
            <!-- Start Plan -->
            <div @class([
                'flex flex-col p-8 rounded-[2.5rem] border backdrop-blur-3xl transition-all duration-500 shadow-2xl group',
                'border-primary-500/50 bg-primary-500/5 ring-4 ring-primary-500/10' => $company->plan === 'start',
                'border-white/10 bg-white/[0.015] hover:border-white/20 hover:bg-white/[0.03]' => $company->plan !== 'start'
            ])>
                <div class="absolute top-0 left-1/2 -translate-x-1/2 bg-emerald-500 text-white px-3 py-1 text-[9px] font-black italic rounded-b-xl uppercase tracking-widest shadow-md">
                    14 Dias Grátis • Sem Cartão
                </div>
                
                <h3 class="mt-4 mb-2 text-xs font-black uppercase tracking-[0.2em] text-slate-500 italic">Start</h3>
                <p class="text-[11px] font-medium text-slate-400 h-10 italic">Ideal para pequenos negócios validarem a solução.</p>
                
                <div class="flex justify-center items-baseline my-8">
                    <span class="text-5xl font-black text-white italic tracking-tighter">R$ 79<span class="text-2xl">,90</span></span>
                    <span class="ml-2 text-[10px] font-black text-slate-600 uppercase tracking-widest">/mês</span>
                </div>

                <div class="text-center mb-6">
                    <span class="inline-block py-1.5 px-3 bg-primary-900/40 border border-primary-500/30 rounded-lg text-primary-400 text-xs font-black uppercase tracking-widest italic">
                        500 Conversas IA / mês
                    </span>
                </div>

                <ul role="list" class="mb-10 space-y-5 text-left flex-grow">
                    <li class="flex items-center gap-3">
                        <div class="p-1 rounded-lg bg-emerald-500/10 border border-emerald-500/20">
                            <x-heroicon-m-check-circle class="w-4 h-4 text-emerald-500" />
                        </div>
                        <span class="text-xs font-medium text-slate-300">1 Canal</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <div class="p-1 rounded-lg bg-emerald-500/10 border border-emerald-500/20">
                            <x-heroicon-m-check-circle class="w-4 h-4 text-emerald-500" />
                        </div>
                        <span class="text-xs font-medium text-slate-300">1 Atendente</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <div class="p-1 rounded-lg bg-emerald-500/10 border border-emerald-500/20">
                            <x-heroicon-m-check-circle class="w-4 h-4 text-emerald-500" />
                        </div>
                        <span class="text-xs font-medium text-slate-300">1 Chatbot + FAQ</span>
                    </li>
                </ul>
                
                @if($company->plan === 'start')
                    <button disabled class="w-full py-4 rounded-2xl bg-white/5 border border-white/10 text-slate-600 text-[10px] font-black uppercase tracking-widest italic cursor-not-allowed">Plano Ativo</button>
                @else
                    <x-filament::button wire:click="changePlan('start')" color="gray" class="w-full fi-btn-color-gray uppercase italic font-black text-[10px] tracking-widest py-4">Assinar Start</x-filament::button>
                @endif
            </div>

            <!-- Professional Plan -->
            <div @class([
                'flex flex-col p-8 rounded-[2.5rem] border backdrop-blur-3xl transition-all duration-500 shadow-2xl relative overflow-hidden group scale-105 z-20',
                'border-primary-500 bg-primary-500/10 ring-8 ring-primary-500/20' => $company->plan === 'professional',
                'border-primary-500/50 bg-[#020617]/80 border-t-primary-400' => $company->plan !== 'professional'
            ])>
                <div class="absolute top-0 w-full left-0 bg-primary-600 text-center text-white py-1.5 text-[9px] font-black italic uppercase tracking-widest shadow-xl flex justify-center items-center gap-1">
                    <x-heroicon-s-star class="w-3 h-3 text-yellow-300" />
                    Mais escolhido pelas empresas
                </div>
                
                <h3 class="mt-6 mb-2 text-xs font-black uppercase tracking-[0.2em] text-primary-400 italic">Professional</h3>
                <p class="text-[11px] font-medium text-slate-400 h-10 italic">Para empresas que precisam de mais poder e múltiplos canais.</p>
                
                <div class="flex justify-center items-baseline my-8">
                    <span class="text-5xl font-black text-white italic tracking-tighter">R$ 199<span class="text-2xl">,90</span></span>
                    <span class="ml-2 text-[10px] font-black text-slate-600 uppercase tracking-widest">/mês</span>
                </div>

                <div class="text-center mb-6">
                    <span class="inline-block py-1.5 px-3 bg-primary-500 border border-primary-400 rounded-lg text-white shadow-[0_0_15px_rgba(14,165,233,0.4)] text-xs font-black uppercase tracking-widest italic">
                        3.000 Conversas IA / mês
                    </span>
                    <p class="text-[10px] text-primary-300 mt-2 font-bold italic">14 Dias Grátis • Sem Cartão</p>
                </div>

                <ul role="list" class="mb-10 space-y-5 text-left flex-grow">
                    <li class="flex items-center gap-3">
                        <div class="p-1 rounded-lg bg-emerald-500/10 border border-emerald-500/20 shadow-[0_0_10px_rgba(16,185,129,0.2)]">
                            <x-heroicon-m-check-circle class="w-4 h-4 text-emerald-400" />
                        </div>
                        <span class="text-xs font-black text-white italic uppercase tracking-tight">3 Canais</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <div class="p-1 rounded-lg bg-emerald-500/10 border border-emerald-500/20">
                            <x-heroicon-m-check-circle class="w-4 h-4 text-emerald-400" />
                        </div>
                        <span class="text-xs font-black text-white italic uppercase tracking-tight">5 Atendentes</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <div class="p-1 rounded-lg bg-emerald-500/10 border border-emerald-500/20">
                            <x-heroicon-m-check-circle class="w-4 h-4 text-emerald-400" />
                        </div>
                        <span class="text-xs font-medium text-slate-300">3 Chatbots Inteligentes</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <div class="p-1 rounded-lg bg-emerald-500/10 border border-emerald-500/20">
                            <x-heroicon-m-check-circle class="w-4 h-4 text-emerald-400" />
                        </div>
                        <span class="text-xs font-medium text-slate-300">Base de Conhecimento</span>
                    </li>
                </ul>

                @if($company->plan === 'professional' || $company->plan === 'pro')
                    <button disabled class="w-full py-5 rounded-2xl bg-primary-600/20 border border-primary-500/30 text-primary-400 text-[10px] font-black uppercase tracking-widest italic cursor-not-allowed">Contrato Ativo</button>
                @else
                    <x-filament::button wire:click="changePlan('professional')" size="lg" class="w-full fi-btn-color-primary uppercase italic font-black text-[10px] tracking-widest py-5 shadow-xl shadow-primary-600/30">Assinar Professional</x-filament::button>
                @endif
            </div>

            <!-- Enterprise Plan -->
            <div @class([
                'flex flex-col p-8 rounded-[2.5rem] border backdrop-blur-3xl transition-all duration-500 shadow-2xl group z-10',
                'border-primary-500/50 bg-primary-500/5 ring-4 ring-primary-500/10' => $company->plan === 'enterprise',
                'border-white/10 bg-white/[0.015] hover:border-white/20 hover:bg-white/[0.03]' => $company->plan !== 'enterprise'
            ])>
                <h3 class="mb-2 text-xs font-black uppercase tracking-[0.2em] text-slate-500 italic">Enterprise</h3>
                <p class="text-[11px] font-medium text-slate-400 h-10 italic">Solução completa para grandes operações.</p>
                
                <div class="flex flex-col justify-center items-center my-8 h-[68px]">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">A partir de</span>
                    <span class="text-4xl font-black text-white italic tracking-tighter">R$ 999</span>
                </div>

                <div class="text-center mb-6">
                    <span class="inline-block py-1.5 px-3 bg-amber-900/40 border border-amber-500/30 rounded-lg text-amber-400 text-xs font-black uppercase tracking-widest italic">
                        10.000+ Conversas IA
                    </span>
                </div>

                <ul role="list" class="mb-10 space-y-5 text-left flex-grow">
                    <li class="flex items-center gap-3">
                        <div class="p-1 rounded-lg bg-emerald-500/10 border border-emerald-500/20">
                            <x-heroicon-m-check-circle class="w-4 h-4 text-emerald-500" />
                        </div>
                        <span class="text-xs font-medium text-slate-300">10 Canais & 20 Atendentes</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <div class="p-1 rounded-lg bg-emerald-500/10 border border-emerald-500/20">
                            <x-heroicon-m-check-circle class="w-4 h-4 text-emerald-500" />
                        </div>
                        <span class="text-xs font-medium text-slate-300">Integração via API & SLA</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <div class="p-1 rounded-lg bg-amber-500/10 border border-amber-500/20">
                            <x-heroicon-m-paint-brush class="w-4 h-4 text-amber-500" />
                        </div>
                        <span class="text-xs font-black text-amber-500 italic uppercase tracking-widest">White Label</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <div class="p-1 rounded-lg bg-emerald-500/10 border border-emerald-500/20">
                            <x-heroicon-m-check-circle class="w-4 h-4 text-emerald-500" />
                        </div>
                        <span class="text-xs font-medium text-slate-300">Gerente de Conta</span>
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
