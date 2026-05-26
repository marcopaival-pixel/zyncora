<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\RequiresCompanyOrPlatformAdmin;
use App\Models\Conversation;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class AdvancedMetrics extends StatsOverviewWidget
{
    use RequiresCompanyOrPlatformAdmin;

    protected static bool $isLazy = true;

    protected static ?int $sort = 6;

    protected function getHeading(): ?string
    {
        return 'Desempenho do atendimento';
    }

    protected function getDescription(): ?string
    {
        return 'TMR, TMA, taxa de resolução e CSAT (quando configurado), no contexto atual.';
    }

    protected function getStats(): array
    {
        $user = auth()->user();
        $query = Conversation::query();

        if ($user && ! $user->isPlatformAdmin()) {
            $query->where('company_id', $user->company_id);
        }

        // 1. TMA — agregação na BD (evita carregar todas as conversas em memória)
        $tma = '0m';
        $tmaBase = (clone $query)
            ->whereNotNull('started_at')
            ->whereNotNull('closed_at');
        $driver = $tmaBase->getConnection()->getDriverName();

        if ($driver === 'mysql') {
            $avgSeconds = (clone $tmaBase)->avg(DB::raw('TIMESTAMPDIFF(SECOND, started_at, closed_at)'));
            if ($avgSeconds !== null && (float) $avgSeconds > 0) {
                $tma = round((float) $avgSeconds / 60).'m';
            }
        } else {
            $closedConversations = (clone $tmaBase)->limit(2000)->get(['started_at', 'closed_at']);
            if ($closedConversations->count() > 0) {
                $totalSeconds = $closedConversations->sum(function ($c) {
                    return $c->closed_at->diffInSeconds($c->started_at);
                });
                $averageSeconds = $totalSeconds / $closedConversations->count();
                $tma = round($averageSeconds / 60).'m';
            }
        }

        // 2. TMR (Tempo Médio de Resposta) - Usando a nova coluna response_time_seconds
        $avgResponseSeconds = (clone $query)->whereNotNull('response_time_seconds')->avg('response_time_seconds') ?? 0;
        $tmr = $avgResponseSeconds > 60 ? round($avgResponseSeconds / 60).'m' : round($avgResponseSeconds).'s';

        // 3. Volume de Mensagens e Resolução
        $totalConversations = (clone $query)->count();
        $resolutionRate = $totalConversations > 0 ? round(((clone $query)->where('status', 'closed')->count() / $totalConversations) * 100) : 0;

        return [
            Stat::make('TMR (Resposta)', $tmr)
                ->description('Média do primeiro contato')
                ->descriptionIcon('heroicon-m-bolt')
                ->color('danger')
                ->chart([15, 10, 8, 12, 7, 5, 4]),

            Stat::make('TMA (Resolução)', $tma)
                ->description('Tempo Médio de Atendimento')
                ->descriptionIcon('heroicon-m-clock')
                ->color('info')
                ->chart([7, 4, 6, 8, 5, 3, 4]),

            Stat::make('Taxa de Resolução', $resolutionRate.'%')
                ->description('Conversas encerradas/total')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success')
                ->chart([10, 20, 40, 60, 80, 90, 100]),

            Stat::make('Satisfação (CSAT)', 'N/D')
                ->description('Sem agregação configurada')
                ->descriptionIcon('heroicon-m-star')
                ->color('gray'),
        ];
    }
}
