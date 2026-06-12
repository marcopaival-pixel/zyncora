<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\AiAnswerAuditLog;

class AiOrchestratorStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalSaved = AiAnswerAuditLog::sum('tokens_saved_estimated');
        $avoidedLlm = AiAnswerAuditLog::where('source_used', '!=', 'llm_generative')->count();
        $totalInteractions = AiAnswerAuditLog::count();
        $avoidedPercent = $totalInteractions > 0 ? round(($avoidedLlm / $totalInteractions) * 100, 1) : 0;

        return [
            Stat::make('Chamadas de IA Evitadas', $avoidedLlm)
                ->description("{$avoidedPercent}% resolvidos localmente")
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
                
            Stat::make('Conversas Economizadas', number_format($totalSaved, 0, ',', '.'))
                ->description('Conversas não processadas pela Generativa')
                ->color('primary'),

            Stat::make('Interações Totais', number_format($totalInteractions, 0, ',', '.'))
                ->description('Pelo orquestrador inteligente')
                ->color('gray'),
        ];
    }
}
