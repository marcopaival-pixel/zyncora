<x-filament-widgets::widget>
    <x-filament::card class="relative overflow-hidden bg-gradient-to-r from-emerald-500/10 to-emerald-600/5 border border-emerald-500/20 shadow-lg">
        <div class="absolute right-0 top-0 opacity-10 pointer-events-none transform translate-x-1/4 -translate-y-1/4">
            <svg class="w-48 h-48 text-emerald-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        </div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-2xl">🎁</span>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">Teste Gratuito</h2>
                </div>
                <p class="text-gray-600 dark:text-gray-300 text-sm mb-4">
                    Aproveite todos os recursos da plataforma. O seu período de teste expira em <strong class="text-gray-900 dark:text-white">{{ $trialEndAt }}</strong>.
                </p>

                <div class="max-w-md">
                    <div class="flex justify-between items-end mb-1">
                        <span class="text-sm font-medium text-emerald-600 dark:text-emerald-400">Dias restantes: {{ $diasRestantes }} de {{ $totalDias }}</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5 overflow-hidden">
                        <div class="bg-emerald-500 h-2.5 rounded-full transition-all duration-500 ease-in-out" style="width: {{ $progress }}%"></div>
                    </div>
                </div>
            </div>

            <div class="flex-shrink-0 mt-4 md:mt-0">
                <a href="{{ route('filament.admin.pages.upgrade-plan') }}" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-xl text-white bg-emerald-600 hover:bg-emerald-700 shadow-lg shadow-emerald-500/30 transition-all hover:-translate-y-0.5 active:translate-y-0">
                    Fazer Upgrade Agora
                    <svg class="ml-2 -mr-1 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </a>
            </div>
        </div>
    </x-filament::card>
</x-filament-widgets::widget>
