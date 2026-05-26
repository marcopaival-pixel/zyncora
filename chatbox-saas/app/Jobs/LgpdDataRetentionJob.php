<?php

namespace App\Jobs;

use App\Models\Contact;
use App\Models\Conversation;
use App\Models\LgpdSetting;
use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class LgpdDataRetentionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Iniciando limpeza de dados LGPD...");

        $settings = LgpdSetting::where('is_active', true)
            ->where('retention_days', '>', 0)
            ->get();

        foreach ($settings as $setting) {
            $cutoffDate = now()->subDays($setting->retention_days);
            $companyId = $setting->company_id;

            // 1. Mensagens antigas
            $deletedMessages = Message::whereHas('conversation', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })
            ->where('created_at', '<', $cutoffDate)
            ->delete();

            // 2. Conversas antigas (se não tiverem mensagens recentes)
            $deletedConversations = Conversation::where('company_id', $companyId)
                ->where('updated_at', '<', $cutoffDate)
                ->delete();

            if ($deletedMessages > 0 || $deletedConversations > 0) {
                Log::info("Empresa {$companyId}: Removidas {$deletedMessages} mensagens e {$deletedConversations} conversas (Limite: {$setting->retention_days} dias).");
            }
        }
    }
}
