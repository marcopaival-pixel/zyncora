<x-filament-widgets::widget>
    @if ($plan)
        <x-filament::section>
            <x-slot name="heading">
                <span class="flex items-center gap-2">
                    <x-heroicon-o-clipboard-document-list class="h-5 w-5 text-primary-500" />
                    Resumo do plano
                </span>
            </x-slot>

            <x-slot name="description">
                Estado atual na base de dados. Altere os campos abaixo e use «Guardar alterações» para aplicar.
            </x-slot>

            <div class="space-y-6">
                <div class="flex flex-wrap items-end justify-between gap-4 border-b border-gray-200 pb-6 dark:border-white/10">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Valor
                        </p>
                        <p class="mt-1 text-3xl font-black tracking-tight text-gray-900 dark:text-white">
                            <span class="text-primary-500">R$</span> {{ $priceFormatted }}
                            <span class="text-lg font-semibold text-gray-500 dark:text-gray-400">
                                / {{ $intervalLabel === 'Anual' ? 'ano' : 'mês' }}
                            </span>
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @if ($plan->is_active)
                            <x-filament::badge color="success">
                                Ativo
                            </x-filament::badge>
                        @else
                            <x-filament::badge color="gray">
                                Inativo
                            </x-filament::badge>
                        @endif
                        @if ($plan->is_popular)
                            <x-filament::badge color="warning">
                                Popular
                            </x-filament::badge>
                        @endif
                    </div>
                </div>

                <div>
                    <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        Limites
                    </p>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                        <div class="rounded-xl border border-gray-200 bg-gray-50/80 p-3 dark:border-white/10 dark:bg-white/5">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Membros</p>
                            <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $plan->max_users }}</p>
                        </div>
                        <div class="rounded-xl border border-gray-200 bg-gray-50/80 p-3 dark:border-white/10 dark:bg-white/5">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Atendentes</p>
                            <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $plan->max_attendants }}</p>
                        </div>
                        <div class="rounded-xl border border-gray-200 bg-gray-50/80 p-3 dark:border-white/10 dark:bg-white/5">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Canais</p>
                            <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $plan->max_channels }}</p>
                        </div>
                        <div class="rounded-xl border border-gray-200 bg-gray-50/80 p-3 dark:border-white/10 dark:bg-white/5">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Chatbots</p>
                            <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $plan->max_chatbots }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-dashed border-gray-300 bg-gray-50/50 px-4 py-3 dark:border-white/15 dark:bg-white/5">
                    <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                        <x-heroicon-o-sparkles class="h-5 w-5 text-amber-500" />
                        <span>
                            <strong class="text-gray-900 dark:text-white">{{ $featuresCount }}</strong>
                            funcionalidade(s) listada(s) no plano
                        </span>
                    </div>
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        Slug: <code class="rounded bg-gray-200 px-1.5 py-0.5 font-mono text-gray-800 dark:bg-white/10 dark:text-gray-200">{{ $plan->slug }}</code>
                    </span>
                </div>
            </div>
        </x-filament::section>
    @endif
</x-filament-widgets::widget>
