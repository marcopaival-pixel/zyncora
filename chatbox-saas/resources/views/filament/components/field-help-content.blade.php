<div class="prose dark:prose-invert max-w-none">
    <p class="text-gray-600 dark:text-gray-300">
        {{ $description }}
    </p>

    @if(!empty($examples))
        @php
            $company = filament()->getTenant();
            $sectorId = $company?->sector_id;
            $example = null;
            if ($sectorId && isset($examples[$sectorId])) {
                $example = $examples[$sectorId];
            } elseif (isset($examples['default'])) {
                $example = $examples['default'];
            }
        @endphp

        @if($example)
            <div class="mt-6 p-4 bg-primary-50 dark:bg-primary-900/30 rounded-lg border border-primary-200 dark:border-primary-800">
                <h4 class="text-primary-700 dark:text-primary-400 font-bold mb-2 flex items-center gap-2 text-sm">
                    <x-heroicon-o-light-bulb class="w-4 h-4"/> Exemplo Prático de Preenchimento
                </h4>
                <p class="text-primary-900 dark:text-primary-100 text-sm">
                    {{ $example }}
                </p>
            </div>
        @endif
    @endif
</div>
