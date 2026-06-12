<?php

namespace App\Services;

use App\Models\ChatLog;
use App\Models\CompanyIntegration;
use App\Models\Message;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MessengerOutboundDispatcher
{
    public function dispatch(Message $message): void
    {
        $message->loadMissing('conversation.channel');

        $conversation = $message->conversation;
        if (! $conversation) {
            return;
        }

        if (! $conversation->channel || $conversation->channel->type !== 'messenger') {
            return;
        }

        $psid = $conversation->client_phone;
        if (!$psid) {
            return;
        }

        $integration = CompanyIntegration::query()
            ->where('company_id', $conversation->company_id)
            ->where('driver', 'messenger')
            ->where('status', '!=', 'error')
            ->first();

        if (! $integration) {
            return;
        }

        $pageAccessToken = $integration->credentials['page_access_token'] ?? '';

        if ($pageAccessToken === '') {
            Log::info('messenger_outbound_skipped', [
                'reason' => 'missing_page_access_token',
                'company_id' => $conversation->company_id,
            ]);
            return;
        }

        try {
            $version = config('chatbox.messenger.graph_version', 'v21.0');
            $url = "https://graph.facebook.com/{$version}/me/messages?access_token={$pageAccessToken}";
            
            $response = Http::post($url, [
                'recipient' => ['id' => $psid],
                'message' => ['text' => $message->body],
                'messaging_type' => 'RESPONSE'
            ]);

            if ($response->failed()) {
                throw new \Exception("Messenger API Error: " . $response->body());
            }

        } catch (\Throwable $e) {
            Log::warning('messenger_outbound_failed', [
                'message_id' => $message->id,
                'error' => $e->getMessage(),
            ]);

            ChatLog::query()->create([
                'company_id' => $conversation->company_id,
                'log_type' => 'messenger_outbound_error',
                'description' => $e->getMessage(),
                'context' => [
                    'message_id' => $message->id,
                ],
                'logged_at' => now(),
            ]);
        }
    }
}
