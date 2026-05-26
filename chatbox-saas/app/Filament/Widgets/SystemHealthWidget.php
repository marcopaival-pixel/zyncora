<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\SystemErrorLog;
use App\Models\User;
use App\Models\Company;
use App\Models\Conversation;

class SystemHealthWidget extends BaseWidget
{
    protected static bool $isLazy = true;

    protected static ?int $sort = -1; // Top of the page

    protected function getStats(): array
    {
        if (! auth()->user()?->isPlatformAdmin()) {
            return [];
        }

        return [
            $this->getDatabaseStat(),
            $this->getQueueStat(),
            $this->getFailedJobsStat(),
            $this->getRecentErrorsStat(),
            $this->getBroadcastingStat(),
            $this->getBusinessStat(),
        ];
    }

    protected function getDatabaseStat(): Stat
    {
        try {
            DB::connection()->getPdo();
            return Stat::make('Banco de Dados', 'Conectado')
                ->description('MySQL/MariaDB Operational')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success');
        } catch (\Exception $e) {
            return Stat::make('Banco de Dados', 'Erro')
                ->description('Falha na conexão')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger');
        }
    }

    protected function getQueueStat(): Stat
    {
        if (config('queue.default') === 'sync' || ! Schema::hasTable('jobs')) {
            return Stat::make('Fila de Jobs', 'sync')
                ->description('Sem fila persistente')
                ->descriptionIcon('heroicon-m-cpu-chip')
                ->color('gray');
        }

        $pending = DB::table('jobs')->count();
        $warning = (int) config('chatbox.monitoring.queue_pending_warning', 50);
        $critical = (int) config('chatbox.monitoring.queue_pending_critical', 200);
        $color = $pending >= $critical ? 'danger' : ($pending >= $warning ? 'warning' : 'success');

        return Stat::make('Fila de Jobs', $pending)
            ->description($pending > 0 ? 'Processando...' : 'Fila limpa')
            ->descriptionIcon('heroicon-m-cpu-chip')
            ->color($color);
    }

    protected function getFailedJobsStat(): Stat
    {
        if (! Schema::hasTable('failed_jobs')) {
            return Stat::make('Jobs falhados', '—')
                ->description('Tabela indisponível')
                ->color('gray');
        }

        $failed = DB::table('failed_jobs')->count();
        $warning = (int) config('chatbox.monitoring.failed_jobs_warning', 1);
        $color = $failed >= $warning ? 'danger' : 'success';

        return Stat::make('Jobs falhados', $failed)
            ->description($failed > 0 ? 'Rever failed_jobs' : 'Nenhum falhado')
            ->descriptionIcon('heroicon-m-exclamation-triangle')
            ->color($color);
    }

    protected function getRecentErrorsStat(): Stat
    {
        if (! Schema::hasTable('system_error_logs')) {
            return Stat::make('Erros 24h', '—')
                ->description('Log indisponível')
                ->color('gray');
        }

        $count = SystemErrorLog::query()
            ->where('created_at', '>=', now()->subDay())
            ->count();
        $warning = (int) config('chatbox.monitoring.error_log_warning_24h', 10);
        $color = $count >= $warning ? 'warning' : 'success';

        return Stat::make('Erros 24h', $count)
            ->description('SystemErrorLog')
            ->descriptionIcon('heroicon-m-bug-ant')
            ->color($color);
    }

    protected function getBroadcastingStat(): Stat
    {
        $driver = config('broadcasting.default');
        
        return Stat::make('Broadcasting', strtoupper($driver))
            ->description($driver === 'reverb' ? 'Real-time Ativo' : 'Modo Log/Desativado')
            ->descriptionIcon('heroicon-m-signal')
            ->color($driver === 'reverb' ? 'success' : 'gray');
    }

    protected function getBusinessStat(): Stat
    {
        $companies = Company::count();
        $chats = Conversation::where('created_at', '>=', now()->startOfDay())->count();

        return Stat::make('Hoje', "{$chats} Chats")
            ->description("{$companies} Clientes Ativos")
            ->descriptionIcon('heroicon-m-user-group')
            ->color('primary');
    }
}
