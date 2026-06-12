<x-filament-widgets::widget>
    <x-filament::section class="bg-gradient-to-r from-primary-500 to-primary-700 text-white shadow-lg ring-1 ring-primary-500/50">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-white/20 rounded-lg">
                    <x-filament::icon icon="heroicon-o-sparkles" class="w-8 h-8 text-white" />
                </div>
                <div>
                    <h2 class="text-xl font-bold text-white">Desbloqueie todo o poder da Inteligência Artificial</h2>
                    <p class="text-primary-100 mt-1">Sua cota de mensagens automáticas pode se esgotar em breve. Faça upgrade para o plano Ilimitado.</p>
                </div>
            </div>
            
            <div class="flex-shrink-0">
                <x-filament::button
                    wire:click="redirectToStripe"
                    color="warning"
                    size="lg"
                    class="font-bold shadow-sm"
                >
                    Fazer Upgrade Agora
                </x-filament::button>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
