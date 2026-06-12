<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SystemErrorLog;
use App\Models\UserSessionLog;
use App\Models\ActivityLog;

class CleanSystemLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:clean-logs {--days=30 : The number of days to retain logs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpa logs antigos (SystemErrorLog, UserSessionLog, ActivityLog) para manter o banco enxuto';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        $date = now()->subDays($days);

        $this->info("Excluindo logs anteriores a {$date->format('Y-m-d')}...");

        $errors = SystemErrorLog::where('created_at', '<', $date)->delete();
        $this->info("SystemErrorLogs excluídos: $errors");

        $sessions = UserSessionLog::where('created_at', '<', $date)->delete();
        $this->info("UserSessionLogs excluídos: $sessions");

        $activities = ActivityLog::where('created_at', '<', $date)->delete();
        $this->info("ActivityLogs excluídos: $activities");

        $this->info('Limpeza de logs concluída com sucesso!');
    }
}
