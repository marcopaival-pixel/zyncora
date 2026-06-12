<?php

namespace App\Jobs;

use App\Models\Channel;
use App\Models\Conversation;
use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class IngestWebhookPayloadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $channelType,
        public string $externalRef,
        public string $from,
        public array $messageData,
        public string $messageType = 'text',
        public ?int $companyId = null // Se null, tentamos descobrir pelo external_ref
    ) {
    }

    public function handle(): void
    {
        $companyId = $this->companyId;

        // Tentar descobrir a empresa se não vier definida (webhooks universais)
        if (!$companyId) {
            $channel = Cache::remember("{$this->channelType}_channel_{$this->externalRef}", 300, function () {
                return Channel::where('type', $this->channelType)->where('external_ref', $this->externalRef)->first();
            });

            if (!$channel) {
                // Não encontramos empresa para este canal, ignorar silenciosamente.
                return;
            }
            $companyId = $channel->company_id;
        } else {
            $channel = Channel::query()->firstOrCreate(
                [
                    'company_id' => $companyId,
                    'type' => $this->channelType,
                    'external_ref' => $this->externalRef ?: 'default',
                ],
                ['status' => 'connected']
            );
        }

        $lockKey = "{$this->channelType}_inbound_lock_{$companyId}_{$this->from}";

        Cache::lock($lockKey, 10)->block(5, function () use ($companyId, $channel) {
            $conversation = Conversation::query()
                ->where('company_id', $companyId)
                ->where('channel_id', $channel->id)
                ->where('client_phone', $this->from) // Usamos client_phone de forma genérica para IDs do remetente
                ->where('status', '!=', 'closed')
                ->orderByDesc('id')
                ->first();

            if (!$conversation) {
                $conversation = Conversation::query()->create([
                    'company_id' => $companyId,
                    'channel_id' => $channel->id,
                    'visitor_token' => bin2hex(random_bytes(16)),
                    'client_phone' => $this->from,
                    'status' => 'open',
                    'started_at' => now(),
                ]);
            }

            $text = '';
            $mediaId = null;
            $mimeType = null;
            $source = "{$this->channelType}_webhook";

            // Processamento específico de cada canal pode ser inferido pelo $this->channelType ou $this->messageType
            if ($this->messageType === 'text') {
                if ($this->channelType === 'whatsapp') {
                    $text = (string) data_get($this->messageData, 'text.body', '');
                } else {
                    $text = (string) data_get($this->messageData, 'text', '');
                }
            } else {
                // Para WhatsApp media (image, video, document, audio)
                if ($this->channelType === 'whatsapp') {
                    $mediaObj = data_get($this->messageData, $this->messageType, []);
                    $mediaId = data_get($mediaObj, 'id');
                    $mimeType = data_get($mediaObj, 'mime_type');
                }
            }

            // Fallback para texto (Instagram/Messenger já chegam limpos em 'text')
            if (empty($text) && $this->messageType === 'text') {
                $text = (string) data_get($this->messageData, 'text', '');
            }

            $message = $conversation->messages()->create([
                'sender_type' => 'visitor',
                'body' => $text,
                'message_type' => $this->messageType,
                'meta' => [
                    'source' => $source,
                    'media_id' => $mediaId,
                    'mime_type' => $mimeType,
                ],
                'sent_at' => now(),
            ]);

            // Despachar processamento assíncrono para a IA
            ProcessWebhookMessage::dispatch($conversation, $text ?: "[{$this->messageType}]");
        });
    }
}
