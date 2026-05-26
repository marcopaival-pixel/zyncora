<?php

namespace App\Jobs;

use App\Models\Message;
use App\Services\WhatsAppOutboundDispatcher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWhatsAppOutboundJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /**
     * @var list<int>
     */
    public array $backoff = [30, 120];

    public function __construct(public int $messageId)
    {
        $this->onQueue((string) config('chatbox.whatsapp.queue', 'default'));
    }

    public function handle(WhatsAppOutboundDispatcher $dispatcher): void
    {
        $message = Message::query()->find($this->messageId);
        if (! $message) {
            return;
        }

        $dispatcher->dispatch($message);
    }
}
