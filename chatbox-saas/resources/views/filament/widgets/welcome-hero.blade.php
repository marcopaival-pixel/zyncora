<x-filament-widgets::widget>
    @php
        $tenantData = $this->getCompanyData();
        $ctx = $tenantData['context'] ?? 'tenant';
        $isOrphan = $ctx === 'orphan';
        $isPlatform = $ctx === 'platform';
        $isTenant = $ctx === 'tenant';
        $daysLeft = $tenantData['days_left'] ?? null;
        $isExpiring = $isTenant && $daysLeft !== null && $daysLeft <= 5;
        $usersPercent = ($tenantData['max_users'] ?? 0) > 0
            ? min(100, (($tenantData['users_count'] ?? 0) / $tenantData['max_users']) * 100)
            : 0;
    @endphp

    <div class="relative overflow-hidden rounded-[2.5rem] bg-white/[0.015] backdrop-blur-2xl p-8 shadow-2xl border border-white/10 transition-all duration-700 hover:border-primary-500/30 group">
        <!-- Abstract Background Blobs -->
        <div class="pointer-events-none absolute -right-20 -top-20 h-64 w-64 rounded-full bg-primary-500/10 blur-[100px] group-hover:bg-primary-500/20 transition-all duration-700"></div>
        <div class="pointer-events-none absolute -bottom-20 -left-20 h-64 w-64 rounded-full bg-indigo-500/5 blur-[100px] group-hover:bg-indigo-500/10 transition-all duration-700"></div>

        <div class="relative z-10 flex flex-col items-start justify-between gap-8 md:flex-row md:items-center">

            <div class="space-y-4 flex-1">
                <div class="flex flex-wrap items-center gap-4">
                    <div class="inline-flex items-center gap-2 rounded-full border border-primary-500/20 bg-primary-500/10 px-3 py-1 text-[10px] font-black uppercase tracking-[0.2em] text-primary-400">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-primary-500"></span>
                        </span>
                        Painel Operacional
                    </div>
                    
                    @if($isPlatform)
                        <span class="px-3 py-1 text-[10px] font-black uppercase tracking-[0.2em] rounded-full bg-slate-500/10 text-slate-400 border border-slate-500/20">
                            {{ $tenantData['plan'] }}
                        </span>
                    @elseif($isOrphan)
                        <span class="px-3 py-1 text-[10px] font-black uppercase tracking-[0.2em] rounded-full bg-amber-500/10 text-amber-500 border border-amber-500/20">
                            Sem organização
                        </span>
                    @else
                        <span class="px-3 py-1 text-[10px] font-black uppercase tracking-[0.2em] rounded-full {{ $tenantData['plan'] === 'ENTERPRISE' ? 'bg-amber-500/10 text-amber-500 border border-amber-500/20' : 'bg-primary-500/10 text-primary-400 border border-primary-500/20' }}">
                            Plano {{ $tenantData['plan'] }}
                        </span>
                    @endif
                </div>

                <h2 class="text-3xl font-black tracking-tighter text-white uppercase italic md:text-4xl leading-none">
                    {{ $this->getGreeting() }}, <br/>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary-400 to-indigo-400">{{ $this->getUserName() }}</span>
                </h2>

                @if($isOrphan)
                    <p class="max-w-xl text-sm font-medium text-slate-400 leading-relaxed">
                        A sua conta não está associada a uma organização. Peça a um administrador para vincular a sua conta ou contacte o suporte.
                    </p>
                @elseif($isPlatform)
                    <p class="max-w-xl text-sm font-medium text-slate-400 leading-relaxed">
                        Visão global da plataforma: empresas, utilizadores e métricas agregadas. Controle total sobre o ecossistema Zynkora.
                    </p>
                @elseif($isExpiring)
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-red-500/10 border border-red-500/20 rounded-2xl text-red-400 text-xs font-bold mt-2">
                        <x-heroicon-o-exclamation-triangle class="w-4 h-4" />
                        Seu plano expira em {{ $daysLeft }} dias.
                        <a href="{{ \App\Filament\Pages\Billing::getUrl() }}" class="underline hover:text-white ml-2">Renovar agora</a>
                    </div>
                @else
                    <p class="max-w-xl text-sm font-medium text-slate-400 leading-relaxed">
                        Central de atendimento inteligente: gerencie conversas, impulsione seu CRM e escale sua operação com IA generativa.
                    </p>
                @endif
            </div>

            @if($isTenant)
                <div class="flex flex-wrap gap-4 w-full md:w-auto">
                    <div class="flex flex-col bg-white/[0.03] backdrop-blur-xl p-5 rounded-[1.5rem] border border-white/5 min-w-[160px] flex-1 md:flex-none transition-all hover:bg-white/[0.05] hover:border-white/10">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-[9px] font-black text-slate-500 uppercase tracking-[0.2em]">Atendentes</span>
                            <span class="text-xs font-black text-white italic">{{ $tenantData['users_count'] }} / {{ $tenantData['max_users'] }}</span>
                        </div>
                        <div class="h-1 w-full bg-white/5 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-1000 {{ $usersPercent >= 90 ? 'bg-red-500 shadow-[0_0_10px_rgba(239,68,68,0.5)]' : 'bg-primary-500 shadow-[0_0_10px_rgba(139,92,246,0.5)]' }}" style="width: {{ $usersPercent }}%"></div>
                        </div>
                    </div>

                    <div class="flex flex-col bg-white/[0.03] backdrop-blur-xl p-5 rounded-[1.5rem] border border-white/5 min-w-[160px] flex-1 md:flex-none transition-all hover:bg-white/[0.05] hover:border-white/10">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-[9px] font-black text-slate-500 uppercase tracking-[0.2em]">Bots Ativos</span>
                            <span class="text-xs font-black text-emerald-400 italic">{{ $tenantData['chatbots_count'] }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                            </span>
                            <span class="text-[9px] text-emerald-400/80 font-black uppercase tracking-widest">Sistema Ativo</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="relative z-10 mt-8 pt-6 border-t border-white/5 flex flex-wrap items-center gap-4">
            @if($isPlatform)
                <a href="{{ \App\Filament\SuperAdmin\Resources\CompanyResource::getUrl('index', panel: 'super-admin') }}" class="group relative inline-flex items-center gap-2 overflow-hidden rounded-xl bg-primary-600 px-6 py-3 text-xs font-black uppercase tracking-widest text-white shadow-xl shadow-primary-600/20 transition-all hover:bg-primary-500 hover:translate-y-[-2px] active:scale-95">
                    <x-heroicon-o-building-office class="h-4 w-4" />
                    Empresas
                </a>
                <a href="{{ \App\Filament\Resources\UserResource::getUrl('index', panel: 'admin') }}" class="group inline-flex items-center gap-2 rounded-xl bg-white/5 px-6 py-3 text-xs font-black uppercase tracking-widest text-white border border-white/10 transition-all hover:bg-white/10 hover:translate-y-[-2px] active:scale-95">
                    <x-heroicon-o-users class="h-4 w-4" />
                    Usuários
                </a>
            @elseif($isOrphan)
                <span class="text-[10px] font-bold uppercase tracking-widest text-slate-500 italic">Vincule uma organização para habilitar atalhos.</span>
            @else
                <a href="{{ \App\Filament\Pages\AtendimentoProfissional::getUrl(panel: 'admin') }}" class="group relative inline-flex items-center gap-2 overflow-hidden rounded-xl bg-primary-600 px-6 py-3 text-xs font-black uppercase tracking-widest text-white shadow-xl shadow-primary-600/20 transition-all hover:bg-primary-500 hover:translate-y-[-2px] active:scale-95">
                    <x-heroicon-s-chat-bubble-left-right class="h-4 w-4" />
                    Novo Atendimento
                </a>

                <a href="{{ \App\Filament\Resources\ChatbotResource::getUrl('index', panel: 'admin') }}" class="group inline-flex items-center gap-2 rounded-xl bg-white/5 px-6 py-3 text-xs font-black uppercase tracking-widest text-white border border-white/10 transition-all hover:bg-white/10 hover:translate-y-[-2px] active:scale-95">
                    <x-heroicon-o-cpu-chip class="h-4 w-4" />
                    Gerenciar Bots
                </a>
            @endif
        </div>
    </div>
</x-filament-widgets::widget>
