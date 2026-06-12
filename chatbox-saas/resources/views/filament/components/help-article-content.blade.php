@if($article)
    <div class="prose dark:prose-invert max-w-none">
        @if($article->description)
            <p class="text-lg text-gray-500 dark:text-gray-400 mb-6">
                {{ $article->description }}
            </p>
        @endif

        <div class="mt-4">
            {!! $article->content !!}
        </div>

        @php
            $company = filament()->getTenant();
            $sectorId = $company?->sector_id;
            $examples = $article->examples_by_segment ?? [];
            $example = null;
            if ($sectorId && isset($examples[$sectorId])) {
                $example = $examples[$sectorId];
            } elseif (isset($examples['default'])) {
                $example = $examples['default'];
            }
        @endphp

        @if($example)
            <div class="mt-8 p-4 bg-primary-50 dark:bg-primary-900/30 rounded-lg border border-primary-200 dark:border-primary-800">
                <h4 class="text-primary-700 dark:text-primary-400 font-bold mb-2 flex items-center gap-2">
                    <x-heroicon-o-light-bulb class="w-5 h-5"/> Exemplo Prático para o seu Segmento
                </h4>
                <p class="text-primary-900 dark:text-primary-100">
                    {{ $example }}
                </p>
            </div>
        @endif
        
        <div class="mt-8 pt-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <span class="text-sm text-gray-500">Este artigo foi útil?</span>
            <div class="flex gap-2">
                <x-filament::button color="success" size="sm" outlined icon="heroicon-o-hand-thumb-up">Sim</x-filament::button>
                <x-filament::button color="danger" size="sm" outlined icon="heroicon-o-hand-thumb-down">Não</x-filament::button>
            </div>
        </div>
    </div>
@else
    <div class="text-center py-8">
        <x-heroicon-o-document-text class="w-12 h-12 mx-auto text-gray-400 mb-4" />
        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Artigo não encontrado</h3>
        <p class="text-gray-500 dark:text-gray-400 mt-2">
            Ainda não há um tutorial detalhado para o módulo "{{ $moduleName }}". Nossa equipe de suporte está à disposição se você tiver dúvidas.
        </p>
    </div>
@endif
