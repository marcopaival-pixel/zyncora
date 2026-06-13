<x-filament-panels::page>
    <div class="mb-6">
        <h2 class="text-xl font-bold mb-2">Previsões Financeiras</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-filament::card>
                <div class="text-sm text-gray-500 font-medium">Receita Prevista (Mês Atual)</div>
                <div class="text-2xl font-bold text-success-600">
                    R$ {{ number_format(\App\Models\PaymentHistory::where('status', 'paid')->where('type', 'subscription')->where('paid_at', '>=', now()->subDays(30))->sum('amount') * 1.05, 2, ',', '.') }}
                </div>
                <div class="text-xs text-gray-400 mt-1">Estimativa de 5% de crescimento sobre o MRR atual</div>
            </x-filament::card>
            
            <x-filament::card>
                <div class="text-sm text-gray-500 font-medium">Renovações Previstas (Próximos 7 Dias)</div>
                <div class="text-2xl font-bold text-info-600">
                    {{ \App\Models\Company::where('status', 'active')->whereBetween('expires_at', [now(), now()->addDays(7)])->count() }}
                </div>
            </x-filament::card>
            
            <x-filament::card>
                <div class="text-sm text-gray-500 font-medium">Risco de Churn (Inadimplentes)</div>
                <div class="text-2xl font-bold text-danger-600">
                    {{ \App\Models\Company::where('status', 'suspended')->count() }}
                </div>
                <div class="text-xs text-gray-400 mt-1">Empresas com pagamento atrasado a caminho do cancelamento</div>
            </x-filament::card>
        </div>
    </div>

    <h2 class="text-xl font-bold mt-8 mb-4">Oportunidades de Upsell (Uso de IA > 80%)</h2>
    {{ $this->table }}
</x-filament-panels::page>
