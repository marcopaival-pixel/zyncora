<?php

namespace App\Listeners;

use App\Events\MessageCreated;
use App\Jobs\SendWhatsAppOutboundJob;

class QueueWhatsAppOutbound
{
    public function handle(MessageCreated $event): void
    {
        $message = $event->message;

        if (! in_array($message->sender_type, ['agent', 'bot'], true)) {
            return;
        }

        if (($message->message_type ?? 'text') !== 'text') {
            return;
        }

        $message->loadMissing('conversation.channel');

        if (! $message->conversation?->channel || $message->conversation->channel->type !== 'whatsapp') {
            return;
        }

        SendWhatsAppOutboundJob::dispatch($message->id);
    }
}
