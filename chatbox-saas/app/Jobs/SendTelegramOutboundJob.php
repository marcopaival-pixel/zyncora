<?php

namespace App\Jobs;

use App\Models\Message;
use App\Services\TelegramOutboundDispatcher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendTelegramOutboundJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [10, 30, 60];

    public function __construct(
        public int $messageId
    ) {}

    public function handle(TelegramOutboundDispatcher $dispatcher): void
    {
        $message = Message::query()->with('conversation.channel')->find($this->messageId);
        if (! $message) {
            return;
        }

        $dispatcher->dispatch($message);
    }
}
