<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <x-filament::card>
            <div class="text-sm text-gray-500 font-medium">Receita Total</div>
            <div class="text-2xl font-bold">R$ {{ number_format($totalRevenue, 2, ',', '.') }}</div>
        </x-filament::card>

        <x-filament::card>
            <div class="text-sm text-gray-500 font-medium">MRR (Últimos 30 Dias)</div>
            <div class="text-2xl font-bold text-success-600">R$ {{ number_format($mrr, 2, ',', '.') }}</div>
        </x-filament::card>

        <x-filament::card>
            <div class="text-sm text-gray-500 font-medium">Notas Emitidas</div>
            <div class="text-2xl font-bold">{{ $invoicesGenerated }}</div>
        </x-filament::card>

        <x-filament::card>
            <div class="text-sm text-gray-500 font-medium">Notas com Erro</div>
            <div class="text-2xl font-bold text-danger-600">{{ $invoicesFailed }}</div>
        </x-filament::card>
    </div>

    <x-filament::card>
        <h2 class="text-lg font-bold mb-4">Últimos Pagamentos</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr>
                        <th class="border-b p-2">Empresa</th>
                        <th class="border-b p-2">Valor</th>
                        <th class="border-b p-2">Tipo</th>
                        <th class="border-b p-2">Data</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($latestPayments as $payment)
                        <tr>
                            <td class="border-b p-2">{{ $payment->company->name ?? 'N/A' }}</td>
                            <td class="border-b p-2">R$ {{ number_format($payment->amount, 2, ',', '.') }}</td>
                            <td class="border-b p-2">{{ ucfirst($payment->type) }}</td>
                            <td class="border-b p-2">{{ $payment->paid_at?->format('d/m/Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-filament::card>
</x-filament-panels::page>
