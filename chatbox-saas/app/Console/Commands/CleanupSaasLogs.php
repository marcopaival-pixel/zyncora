<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\ChatLog;
use App\Models\LgpdSetting;
use App\Models\SystemErrorLog;
use Illuminate\Console\Command;

class CleanupSaasLogs extends Command
{
    /**
     * O nome e a assinatura do comando.
     */
    protected $signature = 'chatbox:cleanup';

    /**
     * A descrição do comando.
     */
    protected $description = 'Limpa logs antigos baseando-se nas definições de retenção da LGPD por empresa.';

    /**
     * Executa o comando.
     */
    public function handle(): void
    {
        $this->info('Iniciando limpeza de logs do SaaS (Módulo LGPD)...');

        // 1. Limpeza Baseada em Configurações de Empresa (LGPD)
        $settings = LgpdSetting::query()
            ->withoutGlobalScopes()
            ->where('is_active', true)
            ->where('retention_days', '>', 0)
            ->get();

        foreach ($settings as $setting) {
            $days = $setting->retention_days;
            $companyId = $setting->company_id;
            $cutoffDate = now()->subDays($days);

            // Remove logs de atividade
            $activityCount = ActivityLog::query()
                ->withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where('created_at', '<', $cutoffDate)
                ->delete();

            // Remove logs de chat/técnicos
            $chatCount = ChatLog::query()
                ->withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where('logged_at', '<', $cutoffDate)
                ->delete();

            if ($activityCount > 0 || $chatCount > 0) {
                $this->info("Empresa #{$companyId}: Removidos {$activityCount} logs de atividade e {$chatCount} logs de chat (Corte: {$days} dias).");
            }
        }

        // 2. Limpeza de Logs de Sistema Globais (Fixo 30 dias para evitar inchaço da DB)
        $systemCutoff = now()->subDays(30);
        $systemCount = SystemErrorLog::query()
            ->where('created_at', '<', $systemCutoff)
            ->delete();

        if ($systemCount > 0) {
            $this->info("Sistema: Removidos {$systemCount} logs de erro globais com mais de 30 dias.");
        }

        $this->info('Manutenção e limpeza de logs concluída com sucesso.');
    }
}
