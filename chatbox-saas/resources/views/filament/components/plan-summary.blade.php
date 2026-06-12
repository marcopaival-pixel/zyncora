<div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow">
    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Resumo do Plano</h3>
    <div class="mt-4 border-t border-gray-200 dark:border-gray-700 pt-4">
        <dl class="divide-y divide-gray-200 dark:divide-gray-700">
            <div class="py-3 flex justify-between text-sm font-medium">
                <dt class="text-gray-500 dark:text-gray-400">Plano</dt>
                <dd class="text-gray-900 dark:text-white">{{ $plan->name }}</dd>
            </div>
            <div class="py-3 flex justify-between text-sm font-medium">
                <dt class="text-gray-500 dark:text-gray-400">Atendentes Máx.</dt>
                <dd class="text-gray-900 dark:text-white">{{ $plan->max_attendants }}</dd>
            </div>
            <div class="py-3 flex justify-between text-sm font-medium">
                <dt class="text-gray-500 dark:text-gray-400">Canais Máx.</dt>
                <dd class="text-gray-900 dark:text-white">{{ $plan->max_channels }}</dd>
            </div>
            <div class="py-3 flex justify-between text-sm font-medium">
                <dt class="text-gray-500 dark:text-gray-400">Preço</dt>
                <dd class="text-gray-900 dark:text-white">R$ {{ number_format($plan->price, 2, ',', '.') }} / {{ $plan->billing_period == 'yearly' ? 'ano' : 'mês' }}</dd>
            </div>
        </dl>
    </div>
</div>
