<x-filament::widget>
    @php
        $daysRemaining = $this->getDaysRemaining();
        $agent = $this->getPrimaryAgent();
        $company = auth()->user()->company;
    @endphp

    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-primary-600 via-primary-700 to-primary-900 p-8 shadow-xl mb-6">
        <!-- Background decoration -->
        <div class="absolute -right-10 -top-10 h-64 w-64 rounded-full bg-white opacity-5 blur-3xl"></div>
        <div class="absolute -bottom-10 right-20 h-40 w-40 rounded-full bg-primary-400 opacity-20 blur-2xl"></div>

        <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6 text-white">
            <div class="flex-1">
                <div class="inline-flex items-center rounded-full bg-white/20 px-3 py-1 text-sm font-medium text-white backdrop-blur-md mb-4 border border-white/10 shadow-sm">
                    <span class="flex h-2 w-2 rounded-full bg-green-400 mr-2 animate-pulse"></span>
                    Status: Pronto para uso
                </div>
                <h1 class="text-3xl font-bold tracking-tight text-white mb-2">
                    Bem-vindo à Zynkora
                </h1>
                <p class="text-primary-100 text-lg max-w-2xl">
                    Seu agente inteligente ({{ $agent?->name ?? 'Assistente Virtual' }}) já está configurado para o segmento de <strong>{{ $company?->segment ?? 'sua empresa' }}</strong> e pronto para começar a atender.
                </p>
            </div>
            
            <div class="flex shrink-0 gap-3 flex-wrap md:flex-nowrap">
                <x-filament::button tag="a" href="{{ route('filament.admin.resources.chatbots.edit', $agent ?? 0) }}" color="gray" icon="heroicon-m-adjustments-horizontal" class="bg-white/10 hover:bg-white/20 text-white border-none ring-1 ring-white/20 backdrop-blur-md shadow-lg">
                    Configurar Agente
                </x-filament::button>
            </div>
        </div>
    </div>

    <!-- Cards Grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
        <a href="{{ route('filament.admin.resources.chatbots.index') }}" class="group flex flex-col items-center justify-center p-6 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md hover:border-primary-300 dark:hover:border-primary-700 transition-all duration-200 text-center relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-b from-transparent to-primary-50/50 dark:to-primary-900/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="w-12 h-12 rounded-full bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-300">
                <span class="text-2xl">🤖</span>
            </div>
            <h3 class="font-semibold text-gray-900 dark:text-white text-sm">Agente</h3>
            <p class="text-xs text-gray-500 mt-1">{{ $agent?->name ?? 'Ativo' }}</p>
        </a>

        <a href="{{ route('filament.admin.resources.knowledge-bases.index') }}" class="group flex flex-col items-center justify-center p-6 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md hover:border-primary-300 dark:hover:border-primary-700 transition-all duration-200 text-center relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-b from-transparent to-primary-50/50 dark:to-primary-900/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="w-12 h-12 rounded-full bg-purple-50 dark:bg-purple-900/30 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-300">
                <span class="text-2xl">📚</span>
            </div>
            <h3 class="font-semibold text-gray-900 dark:text-white text-sm">Conhecimento</h3>
            <p class="text-xs text-gray-500 mt-1">{{ $this->getKnowledgeCount() }} Tópicos</p>
        </a>

        <a href="{{ route('filament.admin.resources.conversations.index') }}" class="group flex flex-col items-center justify-center p-6 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md hover:border-primary-300 dark:hover:border-primary-700 transition-all duration-200 text-center relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-b from-transparent to-primary-50/50 dark:to-primary-900/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="w-12 h-12 rounded-full bg-green-50 dark:bg-green-900/30 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-300">
                <span class="text-2xl">💬</span>
            </div>
            <h3 class="font-semibold text-gray-900 dark:text-white text-sm">Conversas</h3>
            <p class="text-xs text-gray-500 mt-1">Acompanhar</p>
        </a>

        <a href="{{ route('filament.admin.resources.chatbots.index') }}" class="group flex flex-col items-center justify-center p-6 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md hover:border-primary-300 dark:hover:border-primary-700 transition-all duration-200 text-center relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-b from-transparent to-primary-50/50 dark:to-primary-900/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="w-12 h-12 rounded-full bg-orange-50 dark:bg-orange-900/30 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-300">
                <span class="text-2xl">🌐</span>
            </div>
            <h3 class="font-semibold text-gray-900 dark:text-white text-sm">Widget</h3>
            <p class="text-xs text-gray-500 mt-1">Instalação</p>
        </a>

        <a href="{{ route('filament.admin.pages.dashboard') }}" class="group flex flex-col items-center justify-center p-6 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md hover:border-primary-300 dark:hover:border-primary-700 transition-all duration-200 text-center relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-b from-transparent to-primary-50/50 dark:to-primary-900/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="w-12 h-12 rounded-full bg-rose-50 dark:bg-rose-900/30 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-300">
                <span class="text-2xl">📊</span>
            </div>
            <h3 class="font-semibold text-gray-900 dark:text-white text-sm">Estatísticas</h3>
            <p class="text-xs text-gray-500 mt-1">Visão Geral</p>
        </a>

        <a href="{{ route('filament.admin.pages.billing') }}" class="group flex flex-col items-center justify-center p-6 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md hover:border-primary-300 dark:hover:border-primary-700 transition-all duration-200 text-center relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-b from-transparent to-primary-50/50 dark:to-primary-900/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="w-12 h-12 rounded-full bg-slate-50 dark:bg-slate-900/30 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-300">
                <span class="text-2xl">⚙️</span>
            </div>
            <h3 class="font-semibold text-gray-900 dark:text-white text-sm">Configurações</h3>
            <p class="text-xs text-gray-500 mt-1">Empresa</p>
        </a>
    </div>
</x-filament::widget>
