<?php

namespace App\Services;

use App\Models\ChatLog;
use App\Models\CompanyIntegration;
use App\Models\Message;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramOutboundDispatcher
{
    public function dispatch(Message $message): void
    {
        $message->loadMissing('conversation.channel');

        $conversation = $message->conversation;
        if (! $conversation) {
            return;
        }

        if (! $conversation->channel || $conversation->channel->type !== 'telegram') {
            return;
        }

        $chatId = $conversation->client_phone;
        if (! $chatId) {
            return;
        }

        $integration = CompanyIntegration::query()
            ->where('company_id', $conversation->company_id)
            ->where('driver', 'telegram')
            ->where('status', '!=', 'error')
            ->first();

        if (! $integration) {
            return;
        }

        $botToken = $integration->credentials['bot_token'] ?? '';

        if ($botToken === '') {
            Log::info('telegram_outbound_skipped', [
                'reason' => 'missing_bot_token',
                'company_id' => $conversation->company_id,
            ]);

            return;
        }

        try {
            $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
            $response = Http::post($url, [
                'chat_id' => $chatId,
                'text' => $message->body,
                'parse_mode' => 'Markdown',
            ]);

            if ($response->failed()) {
                throw new \Exception('Telegram API Error: '.$response->body());
            }

        } catch (\Throwable $e) {
            Log::warning('telegram_outbound_failed', [
                'message_id' => $message->id,
                'error' => $e->getMessage(),
            ]);

            ChatLog::query()->create([
                'company_id' => $conversation->company_id,
                'log_type' => 'telegram_outbound_error',
                'description' => $e->getMessage(),
                'context' => [
                    'message_id' => $message->id,
                ],
                'logged_at' => now(),
            ]);
        }
    }
}
