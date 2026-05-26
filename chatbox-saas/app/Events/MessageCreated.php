<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Disparado após persistir uma mensagem.
 * Com BROADCAST_CONNECTION=reverb (ou pusher), os clientes Echo podem ouvir os canais.
 */
class MessageCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Message $message) {}

    /**
     * @return array<int, \Illuminate\Broadcasting\Channel|\Illuminate\Broadcasting\PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [
            // Canal privado para atendentes no painel admin
            new PrivateChannel('conversation.'.$this->message->conversation_id),
            
            // Canal público seguro para o widget do visitante
            new Channel('conversation.v2.'.$this->message->conversation_id.'.'.$this->message->conversation->visitor_token),
            
            // Canal da empresa para notificações globais
            new PrivateChannel('company.'.$this->message->conversation->company_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.created';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $m = $this->message->fresh() ?? $this->message;

        return [
            'message' => [
                'id' => $m->id,
                'conversation_id' => $m->conversation_id,
                'sender_type' => $m->sender_type,
                'sender_id' => $m->sender_id,
                'body' => $m->body,
                'message_type' => $m->message_type,
                'sent_at' => $m->sent_at?->toIso8601String(),
            ],
        ];
    }
}
