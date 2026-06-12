<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Message;
use App\Models\ChatbotFlowSuggestion;
use Illuminate\Support\Facades\DB;

class ProcessChatbotSuggestions extends Command
{
    protected $signature = 'chatbot:process-suggestions';
    protected $description = 'Processa as últimas mensagens não reconhecidas e sugere novos fluxos.';

    public function handle()
    {
        $this->info('Iniciando processamento de sugestões...');

        $recentMessages = Message::where('sender_type', 'user')
            ->where('sent_at', '>=', now()->subDay())
            ->with('conversation')
            ->get();

        $grouped = [];
        foreach ($recentMessages as $msg) {
            if (!$msg->conversation || !$msg->conversation->chatbot_id) continue;
            
            $text = mb_strtolower(trim($msg->body));
            if (strlen($text) < 5) continue;
            
            $cid = $msg->conversation->chatbot_id;
            if (!isset($grouped[$cid][$text])) {
                $grouped[$cid][$text] = [
                    'company_id' => $msg->conversation->company_id,
                    'count' => 0,
                    'examples' => [],
                ];
            }
            $grouped[$cid][$text]['count']++;
            if (count($grouped[$cid][$text]['examples']) < 5) {
                $grouped[$cid][$text]['examples'][] = $msg->body;
            }
        }

        foreach ($grouped as $chatbotId => $texts) {
            foreach ($texts as $intent => $data) {
                if ($data['count'] > 2) { 
                    ChatbotFlowSuggestion::updateOrCreate([
                        'chatbot_id' => $chatbotId,
                        'suggested_intent' => $intent,
                        'status' => 'pending',
                    ], [
                        'company_id' => $data['company_id'],
                        'message_count' => DB::raw("message_count + {$data['count']}"),
                        'examples' => $data['examples'],
                    ]);
                }
            }
        }
        $this->info('Sugestões processadas com sucesso.');
    }
}
