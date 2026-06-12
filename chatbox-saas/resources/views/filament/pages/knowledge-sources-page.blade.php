<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @php
            $cards = [
                ['type' => 'company_data', 'title' => 'Dados da Empresa', 'desc' => 'Horários, telefones e informações básicas.', 'icon' => 'heroicon-o-building-office'],
                ['type' => 'faq', 'title' => 'FAQ', 'desc' => 'Perguntas e respostas frequentes.', 'icon' => 'heroicon-o-question-mark-circle'],
                ['type' => 'documents', 'title' => 'Documentos', 'desc' => 'Upload de PDFs e Manuais.', 'icon' => 'heroicon-o-document-text'],
                ['type' => 'website', 'title' => 'Site', 'desc' => 'Leitura automática do site.', 'icon' => 'heroicon-o-globe-alt'],
                ['type' => 'external_api', 'title' => 'API Externa', 'desc' => 'Consulta em tempo real a sistemas externos.', 'icon' => 'heroicon-o-server'],
            ];
        @endphp

        @foreach($cards as $card)
            @php
                $isActive = $sources[$card['type']]['is_active'] ?? false;
            @endphp
            <div class="p-6 rounded-3xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-900 shadow-sm relative overflow-hidden flex flex-col">
                <div class="flex justify-between items-start mb-4">
                    <div class="p-3 rounded-2xl {{ $isActive ? 'bg-primary-500/10' : 'bg-gray-100 dark:bg-white/5' }}">
                        @svg($card['icon'], "w-6 h-6 " . ($isActive ? 'text-primary-500' : 'text-gray-400'))
                    </div>
                    <button wire:click="toggleSource('{{ $card['type'] }}')" 
                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-600 focus:ring-offset-2 {{ $isActive ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700' }}" 
                            role="switch" aria-checked="{{ $isActive ? 'true' : 'false' }}">
                        <span aria-hidden="true" class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $isActive ? 'translate-x-5' : 'translate-x-0' }}"></span>
                    </button>
                </div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">{{ $card['title'] }}</h3>
                <p class="text-gray-500 dark:text-gray-400 text-sm mb-4 flex-grow">{{ $card['desc'] }}</p>
                
                @if(in_array($card['type'], ['faq', 'documents', 'website', 'knowledge_base']))
                <x-filament::button tag="a" href="{{ \App\Filament\Resources\KnowledgeBaseResource::getUrl() }}" color="gray" size="sm" class="mt-auto w-full">
                    Gerenciar Conteúdo
                </x-filament::button>
                @endif
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
