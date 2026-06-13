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
    <div class="text-center py-10 px-4 bg-primary-50/50 dark:bg-primary-900/10 border border-primary-100 dark:border-primary-900/50 rounded-2xl mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-primary-100 dark:bg-primary-900/30 mb-4 text-primary-600 dark:text-primary-400">
            <x-heroicon-o-sparkles class="w-8 h-8" />
        </div>
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
            Precisa de ajuda com o módulo "{{ $moduleName }}"?
        </h3>
        <p class="text-gray-600 dark:text-gray-400 max-w-md mx-auto text-sm leading-relaxed">
            Ainda estamos preparando um guia detalhado para esta tela. Enquanto isso, fique à vontade para falar com nossa equipe ou consultar as dúvidas mais comuns abaixo!
        </p>
    </div>
@endif

<div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Ainda precisa de ajuda?</h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="flex flex-col items-center justify-center text-center p-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
            <x-heroicon-o-chat-bubble-left-ellipsis class="w-8 h-8 text-primary-500 mb-2"/>
            <h4 class="font-medium text-gray-900 dark:text-white">Falar com Suporte</h4>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Converse com nossa equipe no WhatsApp.</p>
            <x-filament::button type="button" onclick="alert('Link do WhatsApp do suporte será configurado aqui.')" color="success" icon="heroicon-o-chat-bubble-left-right" class="w-full">
                Chamar no WhatsApp
            </x-filament::button>
        </div>

        <div class="flex flex-col items-center justify-center text-center p-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
            <x-heroicon-o-ticket class="w-8 h-8 text-primary-500 mb-2"/>
            <h4 class="font-medium text-gray-900 dark:text-white">Abrir Chamado</h4>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Relate um problema ou solicitação.</p>
            <x-filament::button type="button" onclick="alert('Módulo de tickets de suporte está em construção.')" color="primary" icon="heroicon-o-envelope" class="w-full" outlined>
                Abrir Ticket
            </x-filament::button>
        </div>
    </div>

    <div class="bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-gray-700 rounded-xl p-4">
        <h4 class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
            <x-heroicon-o-question-mark-circle class="w-5 h-5 text-gray-500"/>
            Dúvidas Frequentes (FAQ) do Sistema
        </h4>
        <div class="space-y-2 text-sm">
            
            <div x-data="{ expanded: false }" class="border-b border-gray-200 dark:border-gray-700 last:border-0 pb-2 last:pb-0">
                <button type="button" @click="expanded = !expanded" class="w-full text-left text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 font-medium flex items-center justify-between gap-2 py-1">
                    <span class="flex items-start gap-2">
                        <x-heroicon-m-chevron-right class="w-4 h-4 mt-0.5 flex-shrink-0 transition-transform duration-200" x-bind:class="expanded ? 'rotate-90' : ''" />
                        Como o Chatbot aprende as respostas da minha empresa (IA)?
                    </span>
                </button>
                <div x-show="expanded" x-collapse x-cloak class="pl-6 pr-2 py-2 text-gray-600 dark:text-gray-400 text-sm">
                    Você pode treinar a Inteligência Artificial acessando o menu <strong>"Base de Conhecimento"</strong>. Basta adicionar os textos, regras e informações da sua empresa. O Chatbot usará esses dados para formular respostas humanizadas aos clientes.
                </div>
            </div>

            <div x-data="{ expanded: false }" class="border-b border-gray-200 dark:border-gray-700 last:border-0 pb-2 last:pb-0">
                <button type="button" @click="expanded = !expanded" class="w-full text-left text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 font-medium flex items-center justify-between gap-2 py-1">
                    <span class="flex items-start gap-2">
                        <x-heroicon-m-chevron-right class="w-4 h-4 mt-0.5 flex-shrink-0 transition-transform duration-200" x-bind:class="expanded ? 'rotate-90' : ''" />
                        Onde visualizo os leads capturados pelo bot?
                    </span>
                </button>
                <div x-show="expanded" x-collapse x-cloak class="pl-6 pr-2 py-2 text-gray-600 dark:text-gray-400 text-sm">
                    Todos os contatos que interagem com o bot e fornecem dados vão automaticamente para o nosso <strong>CRM</strong>. Você pode acompanhar a jornada deles acessando o menu "Pipeline" ou visualizando os "Negócios" (Deals) gerados.
                </div>
            </div>

            <div x-data="{ expanded: false }" class="border-b border-gray-200 dark:border-gray-700 last:border-0 pb-2 last:pb-0">
                <button type="button" @click="expanded = !expanded" class="w-full text-left text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 font-medium flex items-center justify-between gap-2 py-1">
                    <span class="flex items-start gap-2">
                        <x-heroicon-m-chevron-right class="w-4 h-4 mt-0.5 flex-shrink-0 transition-transform duration-200" x-bind:class="expanded ? 'rotate-90' : ''" />
                        Como conectar o meu número de WhatsApp?
                    </span>
                </button>
                <div x-show="expanded" x-collapse x-cloak class="pl-6 pr-2 py-2 text-gray-600 dark:text-gray-400 text-sm">
                    Para conectar seu WhatsApp, vá em "Canais", clique para adicionar um novo canal do tipo WhatsApp e faça a leitura do QR Code exibido na tela, da mesma forma que você conecta o WhatsApp Web.
                </div>
            </div>

            <div x-data="{ expanded: false }" class="border-b border-gray-200 dark:border-gray-700 last:border-0 pb-2 last:pb-0">
                <button type="button" @click="expanded = !expanded" class="w-full text-left text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 font-medium flex items-center justify-between gap-2 py-1">
                    <span class="flex items-start gap-2">
                        <x-heroicon-m-chevron-right class="w-4 h-4 mt-0.5 flex-shrink-0 transition-transform duration-200" x-bind:class="expanded ? 'rotate-90' : ''" />
                        O sistema cumpre os requisitos da LGPD?
                    </span>
                </button>
                <div x-show="expanded" x-collapse x-cloak class="pl-6 pr-2 py-2 text-gray-600 dark:text-gray-400 text-sm">
                    Sim! O sistema conta com um módulo dedicado à LGPD. Em "LGPD", você pode gerenciar os Termos de Consentimento (LgpdConsent), auditar logs (LgpdAudit) e responder rapidamente a solicitações de exclusão de dados dos clientes.
                </div>
            </div>

        </div>
        <div class="mt-4 text-center">
            <a href="javascript:void(0)" onclick="alert('A Central de Ajuda completa está sendo construída.')" class="text-sm text-primary-600 dark:text-primary-400 font-medium hover:underline">Ver central de ajuda completa &rarr;</a>
        </div>
    </div>
</div>
