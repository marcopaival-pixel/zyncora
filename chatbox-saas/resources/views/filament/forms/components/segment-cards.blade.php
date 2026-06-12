<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div x-data="{ state: $wire.$entangle('{{ $getStatePath() }}').live }" class="w-full">
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 -mt-1">
            Utilizamos essa informação para criar automaticamente um agente personalizado para o seu negócio.
        </p>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
            @php
                $segments = [
                    'Saúde' => 'heroicon-o-heart',
                    'Fitness' => 'heroicon-o-bolt',
                    'Educação' => 'heroicon-o-academic-cap',
                    'Jurídico' => 'heroicon-o-scale',
                    'Contabilidade' => 'heroicon-o-calculator',
                    'Imobiliário' => 'heroicon-o-home-modern',
                    'Automotivo' => 'heroicon-o-truck',
                    'Comércio' => 'heroicon-o-building-storefront',
                    'E-commerce' => 'heroicon-o-shopping-bag',
                    'Alimentação' => 'heroicon-o-cake',
                    'Beleza' => 'heroicon-o-sparkles',
                    'Hotelaria e Turismo' => 'heroicon-o-map',
                    'Serviços' => 'heroicon-o-wrench-screwdriver',
                    'Outro' => 'heroicon-o-squares-plus',
                ];
            @endphp
            @foreach($segments as $label => $icon)
                @php
                    $value = $label;
                @endphp
                <button
                    type="button"
                    x-on:click="state = '{{ $value }}'"
                    :class="state === '{{ $value }}' ? 'border-primary-500 ring-2 ring-primary-500 bg-primary-50 dark:bg-primary-900/20 shadow-sm' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-primary-300 dark:hover:border-primary-600 hover:shadow-sm'"
                    class="flex flex-col items-center justify-center p-4 rounded-xl border-2 transition-all duration-200 group"
                >
                    <x-dynamic-component :component="$icon" 
                        class="w-7 h-7 mb-2 transition-colors duration-200" 
                        x-bind:class="state === '{{ $value }}' ? 'text-primary-600 dark:text-primary-400' : 'text-gray-500 dark:text-gray-400 group-hover:text-primary-500'" 
                    />
                    <span 
                        class="font-medium text-xs sm:text-sm text-center transition-colors duration-200"
                        x-bind:class="state === '{{ $value }}' ? 'text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 group-hover:text-primary-600'"
                    >
                        {{ $label }}
                    </span>
                </button>
            @endforeach
        </div>
    </div>
</x-dynamic-component>
