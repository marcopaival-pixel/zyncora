<x-filament-panels::page>
    <div class="premium-container relative">
        @if($company && ($company->plan === 'basic' || empty($company->plan)))
            <!-- Locked State / Upsell -->
            <div class="relative overflow-hidden rounded-[2.5rem] bg-slate-900 border border-white/10 p-10 text-center shadow-2xl backdrop-blur-sm group">
                <div class="absolute inset-0 bg-gradient-to-br from-primary-500/20 to-transparent opacity-50 blur-xl pointer-events-none"></div>
                
                <div class="relative z-10 flex flex-col items-center justify-center py-12">
                    <div class="mb-6 p-4 rounded-full bg-primary-500/10 border border-primary-500/30 animate-pulse">
                        <x-heroicon-o-lock-closed class="w-12 h-12 text-primary-400" />
                    </div>
                    
                    <h2 class="text-3xl font-black text-white uppercase italic tracking-tight mb-4">Acesso a Recursos Avançados</h2>
                    <p class="text-slate-400 max-w-lg mb-8 text-sm font-medium leading-relaxed">
                        Desbloqueie ferramentas avançadas, como integrações ilimitadas, IA Generativa e relatórios aprofundados para escalar o seu negócio.
                    </p>
                    
                    <a href="{{ \App\Filament\Pages\UpgradePlan::getUrl() }}" class="inline-flex items-center gap-2 px-8 py-4 rounded-2xl bg-primary-600 text-white font-black uppercase text-xs tracking-widest italic shadow-[0_0_20px_rgba(99,102,241,0.4)] hover:bg-primary-500 transition-all duration-300 transform hover:scale-105">
                        Fazer Upgrade Agora
                        <x-heroicon-m-arrow-right class="w-4 h-4" />
                    </a>
                </div>
            </div>
        @else
            <!-- Premium Area Unlocked -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Card 1 -->
                <div class="flex flex-col p-6 rounded-3xl border border-primary-500/30 bg-primary-500/5 shadow-xl relative overflow-hidden group hover:bg-primary-500/10 transition-colors">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-primary-500/20 blur-[50px] rounded-full pointer-events-none"></div>
                    <div class="mb-4 p-3 rounded-2xl bg-white/5 inline-block w-fit">
                        <x-heroicon-o-sparkles class="w-6 h-6 text-primary-400" />
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2">Treinamento de IA</h3>
                    <p class="text-slate-400 text-sm mb-4">Configure o cérebro da sua IA com documentos e o histórico da sua empresa.</p>
                    @if(auth()->user()?->canManageIntegrations())
                        <x-filament::button tag="a" href="{{ \App\Filament\Pages\KnowledgeSourcesPage::getUrl() }}" color="primary" class="mt-auto w-full italic uppercase tracking-wider text-xs">Configurar</x-filament::button>
                    @else
                        <x-filament::button disabled color="gray" class="mt-auto w-full italic uppercase tracking-wider text-xs opacity-50 cursor-not-allowed">Sem Permissão</x-filament::button>
                    @endif
                </div>

                <!-- Card 2 -->
                <div class="flex flex-col p-6 rounded-3xl border border-emerald-500/30 bg-emerald-500/5 shadow-xl relative overflow-hidden group hover:bg-emerald-500/10 transition-colors">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/20 blur-[50px] rounded-full pointer-events-none"></div>
                    <div class="mb-4 p-3 rounded-2xl bg-white/5 inline-block w-fit">
                        <x-heroicon-o-presentation-chart-line class="w-6 h-6 text-emerald-400" />
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2">Relatórios Avançados</h3>
                    <p class="text-slate-400 text-sm mb-4">Exportações massivas, mapa de calor e análise de sentimentos dos contactos.</p>
                    @if(auth()->user()?->canViewReports())
                        <x-filament::button tag="a" href="{{ \App\Filament\Pages\Reports::getUrl() }}" color="success" class="mt-auto w-full italic uppercase tracking-wider text-xs">Acessar</x-filament::button>
                    @else
                        <x-filament::button disabled color="gray" class="mt-auto w-full italic uppercase tracking-wider text-xs opacity-50 cursor-not-allowed">Sem Permissão</x-filament::button>
                    @endif
                </div>

                <!-- Card 3 -->
                <div class="flex flex-col p-6 rounded-3xl border border-amber-500/30 bg-amber-500/5 shadow-xl relative overflow-hidden group hover:bg-amber-500/10 transition-colors">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-amber-500/20 blur-[50px] rounded-full pointer-events-none"></div>
                    <div class="mb-4 p-3 rounded-2xl bg-white/5 inline-block w-fit">
                        <x-heroicon-o-rocket-launch class="w-6 h-6 text-amber-400" />
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2">Automação Customizada</h3>
                    <p class="text-slate-400 text-sm mb-4">Crie fluxos de mensagens via webhooks para o seu sistema.</p>
                    @if(auth()->user()?->canManageIntegrations())
                        <x-filament::button tag="a" href="{{ \App\Filament\Resources\ChatbotFlowResource::getUrl() }}" color="warning" class="mt-auto w-full italic uppercase tracking-wider text-xs">Criar Fluxo</x-filament::button>
                    @else
                        <x-filament::button disabled color="gray" class="mt-auto w-full italic uppercase tracking-wider text-xs opacity-50 cursor-not-allowed">Sem Permissão</x-filament::button>
                    @endif
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
