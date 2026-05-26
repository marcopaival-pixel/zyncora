<?php

namespace App\Services;

use App\Models\ChatLog;
use App\Models\CompanyIntegration;
use App\Models\Message;
use Illuminate\Support\Facades\Log;

class WhatsAppOutboundDispatcher
{
    public function __construct(
        protected WhatsAppCloudClient $client
    ) {}

    public function dispatch(Message $message): void
    {
        $message->loadMissing('conversation.channel');

        $conversation = $message->conversation;
        if (! $conversation) {
            return;
        }

        if (! $conversation->channel || $conversation->channel->type !== 'whatsapp') {
            return;
        }

        if (($message->message_type ?? 'text') !== 'text') {
            return;
        }

        $to = $this->normalizeTo($conversation->client_phone);
        if ($to === '') {
            Log::info('whatsapp_outbound_skipped', [
                'reason' => 'missing_client_phone',
                'message_id' => $message->id,
                'conversation_id' => $conversation->id,
            ]);

            return;
        }

        $integration = CompanyIntegration::query()
            ->where('company_id', $conversation->company_id)
            ->where('driver', 'whatsapp_cloud')
            ->where('status', '!=', 'error')
            ->first();

        if (! $integration) {
            return;
        }

        /** @var array<string, mixed> $creds */
        $creds = $integration->credentials ?? [];
        $phoneNumberId = isset($creds['phone_number_id']) ? (string) $creds['phone_number_id'] : '';
        $accessToken = isset($creds['access_token']) ? (string) $creds['access_token'] : '';

        if ($phoneNumberId === '' || $accessToken === '') {
            Log::info('whatsapp_outbound_skipped', [
                'reason' => 'missing_credentials',
                'company_id' => $conversation->company_id,
            ]);

            return;
        }

        try {
            $this->client->sendText($phoneNumberId, $accessToken, $to, (string) $message->body);
        } catch (\Throwable $e) {
            Log::warning('whatsapp_outbound_failed', [
                'message_id' => $message->id,
                'conversation_id' => $conversation->id,
                'error' => $e->getMessage(),
            ]);

            ChatLog::query()->create([
                'company_id' => $conversation->company_id,
                'log_type' => 'whatsapp_outbound_error',
                'description' => $e->getMessage(),
                'context' => [
                    'message_id' => $message->id,
                    'conversation_id' => $conversation->id,
                ],
                'logged_at' => now(),
            ]);
        }
    }

    protected function normalizeTo(?string $phone): string
    {
        if ($phone === null || $phone === '') {
            return '';
        }

        $digits = preg_replace('/\D+/', '', $phone);

        return $digits ?? '';
    }
}
