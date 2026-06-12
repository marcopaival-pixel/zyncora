<div class="space-y-6">
        
        <!-- Avisos Importantes -->
        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-md shadow-sm dark:bg-blue-900/30 dark:border-blue-700">
            <div class="flex">
                <div class="flex-shrink-0">
                    <x-heroicon-o-information-circle class="h-5 w-5 text-blue-400" />
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700 dark:text-blue-300 font-medium">
                        Os limites do seu plano são renovados mensalmente.
                    </p>
                    <p class="text-sm text-blue-600 dark:text-blue-400 mt-1">
                        O saldo não utilizado não é acumulativo para o próximo mês.
                    </p>
                </div>
            </div>
        </div>

        @if($isTrial)
            <!-- Trial Banner -->
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="w-full md:w-1/2">
                        <h3 class="text-xl font-bold flex items-center gap-2">
                            <x-heroicon-s-sparkles class="w-6 h-6 text-yellow-300" />
                            Período de Teste (Trial)
                        </h3>
                        <p class="text-indigo-100 mt-1">
                            Você está no dia {{ $trialDaysTotal - $trialDaysRemaining }} de {{ $trialDaysTotal }}. Faltam {{ $trialDaysRemaining }} dias para expirar.
                        </p>
                    </div>
                    
                    <div class="w-full md:w-1/3">
                        <div class="flex justify-between text-xs font-semibold mb-1">
                            <span>Progresso do Trial</span>
                            <span>{{ $trialPercentage }}%</span>
                        </div>
                        <div class="w-full bg-indigo-900/50 rounded-full h-2.5">
                            <div class="bg-yellow-400 h-2.5 rounded-full" style="width: {{ $trialPercentage }}%"></div>
                        </div>
                    </div>
                    
                    <div class="w-full md:w-auto text-right">
                        <a href="{{ route('filament.admin.pages.upgrade-plan') }}" class="inline-flex items-center px-4 py-2 bg-white text-indigo-600 font-bold rounded-lg hover:bg-gray-50 transition duration-150 ease-in-out shadow-sm">
                            Fazer Upgrade Agora
                        </a>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Meu Plano Info -->
            <x-filament::section class="lg:col-span-1">
                <x-slot name="heading">
                    <div class="flex items-center gap-2 text-xl">
                        <x-heroicon-o-credit-card class="w-6 h-6 text-primary-500" />
                        Meu Plano
                    </div>
                </x-slot>

                <div class="flex flex-col items-center p-4">
                    <div class="w-20 h-20 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center mb-4">
                        <x-heroicon-o-star class="w-10 h-10 text-primary-600 dark:text-primary-400" />
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $currentPlan->name ?? 'Plano Personalizado' }}
                    </h2>
                    <p class="text-3xl font-extrabold text-primary-600 mt-2">
                        R$ {{ number_format($currentPlan->price ?? 0, 2, ',', '.') }}<span class="text-sm font-normal text-gray-500">/mês</span>
                    </p>
                    
                    <div class="mt-6 w-full space-y-4">
                        <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-800">
                            <span class="text-gray-500 dark:text-gray-400">Status</span>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $company->subscription_status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' }}">
                                {{ ucfirst($company->subscription_status ?? 'Ativo') }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-800">
                            <span class="text-gray-500 dark:text-gray-400">Próxima Cobrança</span>
                            <span class="font-medium text-gray-900 dark:text-white">
                                {{ $company->expires_at ? $company->expires_at->format('d/m/Y') : 'N/A' }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-gray-500 dark:text-gray-400">Data de Renovação</span>
                            <span class="font-medium text-gray-900 dark:text-white">
                                {{ $company->expires_at ? $company->expires_at->format('d/m/Y') : 'N/A' }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="mt-8 w-full">
                        <a href="{{ route('filament.admin.pages.billing') }}" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                            Gerenciar Assinatura
                        </a>
                    </div>
                </div>
            </x-filament::section>

            <!-- Consumo do Mês -->
            <x-filament::section class="lg:col-span-2">
                <x-slot name="heading">
                    <div class="flex items-center gap-2 text-xl">
                        <x-heroicon-o-chart-bar class="w-6 h-6 text-primary-500" />
                        Consumo do Mês
                    </div>
                </x-slot>

                <div class="space-y-8 mt-2">
                    @foreach($usageData as $key => $resource)
                        <div class="relative">
                            <div class="flex justify-between items-end mb-1">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-white">{{ $resource['name'] }}</h4>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $resource['used'] }} utilizados de {{ $resource['unlimited'] ? 'Ilimitado' : $resource['limit'] }} contratados
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span class="text-sm font-bold {{ $resource['percentage'] >= 90 ? 'text-danger-600' : ($resource['percentage'] >= 70 ? 'text-warning-600' : 'text-success-600') }}">
                                        {{ $resource['percentage'] }}%
                                    </span>
                                    @if(!$resource['unlimited'])
                                        <p class="text-xs text-gray-500 mt-1">
                                            Restam: <span class="font-semibold">{{ $resource['remaining'] }}</span>
                                        </p>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                                <div class="h-2.5 rounded-full transition-all duration-500 
                                    {{ $resource['percentage'] >= 100 ? 'bg-danger-600' : 
                                      ($resource['percentage'] >= 90 ? 'bg-danger-500' : 
                                      ($resource['percentage'] >= 70 ? 'bg-warning-400' : 'bg-success-500')) }}" 
                                    style="width: {{ $resource['percentage'] }}%">
                                </div>
                            </div>
                            
                            <!-- Alertas de Limite -->
                            @if($resource['percentage'] >= 100)
                                <p class="text-xs text-danger-600 mt-2 flex items-center gap-1 font-medium">
                                    <x-heroicon-s-exclamation-circle class="w-4 h-4" />
                                    Limite atingido! Algumas funcionalidades podem estar bloqueadas.
                                </p>
                            @elseif($resource['percentage'] >= 90)
                                <p class="text-xs text-danger-500 mt-2 flex items-center gap-1 font-medium">
                                    <x-heroicon-s-exclamation-triangle class="w-4 h-4" />
                                    Atenção: Você atingiu 90% do seu limite.
                                </p>
                            @elseif($resource['percentage'] >= 70)
                                <p class="text-xs text-warning-600 mt-2 flex items-center gap-1">
                                    <x-heroicon-s-information-circle class="w-4 h-4" />
                                    Você já utilizou 70% da sua cota mensal.
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </x-filament::section>
        </div>

        <!-- Painel de Resultados Gerados -->
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2 text-xl">
                    <x-heroicon-o-presentation-chart-line class="w-6 h-6 text-primary-500" />
                    Resultados Gerados
                </div>
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 py-4">
                <div class="flex flex-col items-center p-4 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-100 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow">
                    <div class="p-3 bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 rounded-full mb-3">
                        <x-heroicon-o-chat-bubble-left-right class="w-8 h-8" />
                    </div>
                    <span class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($resultsMetrics['conversations'], 0, ',', '.') }}</span>
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400 mt-1">Conversas Atendidas</span>
                </div>

                <div class="flex flex-col items-center p-4 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-100 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow">
                    <div class="p-3 bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 rounded-full mb-3">
                        <x-heroicon-o-users class="w-8 h-8" />
                    </div>
                    <span class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($resultsMetrics['leads'], 0, ',', '.') }}</span>
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400 mt-1">Leads Capturados</span>
                </div>

                <div class="flex flex-col items-center p-4 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-100 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow">
                    <div class="p-3 bg-purple-100 dark:bg-purple-900/50 text-purple-600 dark:text-purple-400 rounded-full mb-3">
                        <x-heroicon-o-clock class="w-8 h-8" />
                    </div>
                    <span class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($resultsMetrics['hours_saved'], 0, ',', '.') }}h</span>
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400 mt-1">Economia Estimada de Horas</span>
                </div>
            </div>
        </x-filament::section>

        <!-- Benefícios e Upgrade -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Benefícios Contratados -->
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2 text-xl">
                        <x-heroicon-o-check-badge class="w-6 h-6 text-primary-500" />
                        Benefícios Inclusos
                    </div>
                </x-slot>

                <ul class="space-y-3 mt-2">
                    @php
                        $features = $currentPlan->features ?? [];
                        $allFeatures = [
                            'Chatbot Ilimitado' => in_array('chatbots', $features),
                            'Integração IA' => in_array('ai', $features),
                            'Agendamentos' => in_array('scheduling', $features),
                            'Relatórios Avançados' => in_array('reports', $features),
                            'WhatsApp Oficial' => in_array('whatsapp_official', $features),
                            'Acesso a API' => in_array('api', $features),
                            'Múltiplas Unidades' => in_array('multi_branch', $features),
                            'Suporte Prioritário' => in_array('priority_support', $features),
                        ];
                    @endphp

                    @foreach($allFeatures as $name => $included)
                        <li class="flex items-center gap-3">
                            @if($included)
                                <div class="flex-shrink-0 w-6 h-6 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                                    <x-heroicon-s-check class="w-4 h-4 text-green-600 dark:text-green-400" />
                                </div>
                                <span class="text-gray-900 dark:text-white font-medium">{{ $name }}</span>
                            @else
                                <div class="flex-shrink-0 w-6 h-6 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                                    <x-heroicon-s-x-mark class="w-4 h-4 text-gray-400 dark:text-gray-500" />
                                </div>
                                <span class="text-gray-500 dark:text-gray-400 line-through">{{ $name }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </x-filament::section>

            <!-- Recursos para Upgrade -->
            <x-filament::section class="bg-gradient-to-br from-gray-50 to-primary-50/50 dark:from-gray-900 dark:to-primary-900/20">
                <x-slot name="heading">
                    <div class="flex items-center gap-2 text-xl">
                        <x-heroicon-o-rocket-launch class="w-6 h-6 text-primary-500" />
                        Recursos Avançados
                    </div>
                </x-slot>

                <div class="h-full flex flex-col justify-center items-center text-center p-6 space-y-4">
                    <div class="p-4 bg-primary-100 dark:bg-primary-900/50 rounded-full">
                        <x-heroicon-o-arrow-trending-up class="w-12 h-12 text-primary-600 dark:text-primary-400" />
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Desbloqueie Todo o Potencial</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm max-w-sm">
                        Faça upgrade para ter acesso a WhatsApp Oficial, API Integrada, Relatórios Avançados e limites muito maiores para o seu time.
                    </p>
                    <a href="{{ route('filament.admin.pages.upgrade-plan') }}" class="mt-4 px-6 py-2.5 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 focus:ring-4 focus:ring-primary-500/20 transition-all shadow-md hover:shadow-lg">
                        Ver Planos de Upgrade
                    </a>
                </div>
            </x-filament::section>
        </div>
    </div>
