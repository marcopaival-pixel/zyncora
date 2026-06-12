<x-filament-panels::page>
    @livewire(\App\Filament\Widgets\AiOrchestratorStatsWidget::class)

    <div class="mt-8">
        {{ $this->table }}
    </div>
</x-filament-panels::page>
