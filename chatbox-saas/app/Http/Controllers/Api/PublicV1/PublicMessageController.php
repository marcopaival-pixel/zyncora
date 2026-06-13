<?php

namespace App\Http\Controllers\Api\PublicV1;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\Conversation;
use App\Services\WhatsAppOutboundDispatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicMessageController extends Controller
{
    /**
     * Dispara uma nova mensagem ativa via API para um contato.
     */
    public function send(Request $request, WhatsAppOutboundDispatcher $dispatcher): JsonResponse
    {
        $data = $request->validate([
            'channel_id' => ['required', 'exists:channels,id'],
            'client_phone' => ['required', 'string'],
            'body' => ['required', 'string'],
        ]);

        $companyId = $request->user()->company_id;

        $channel = Channel::query()
            ->where('company_id', $companyId)
            ->where('id', $data['channel_id'])
            ->firstOrFail();

        // Encontra ou cria conversa
        $conversation = Conversation::query()
            ->where('company_id', $companyId)
            ->where('channel_id', $channel->id)
            ->where('client_phone', $data['client_phone'])
            ->where('status', '!=', 'closed')
            ->first();

        if (! $conversation) {
            $conversation = Conversation::query()->create([
                'company_id' => $companyId,
                'channel_id' => $channel->id,
                'visitor_token' => bin2hex(random_bytes(16)),
                'client_phone' => $data['client_phone'],
                'status' => 'open',
                'started_at' => now(),
            ]);
        }

        $message = $conversation->messages()->create([
            'sender_type' => 'agent', // API treats as system/agent
            'sender_id' => $request->user()->id,
            'body' => $data['body'],
            'message_type' => 'text',
            'meta' => ['source' => 'api_public'],
            'sent_at' => now(),
        ]);

        // Dispara o envio dependendo do tipo do canal
        if ($channel->type === 'whatsapp') {
            $dispatcher->dispatch($message);
        }

        return response()->json([
            'success' => true,
            'message' => 'Mensagem enfileirada com sucesso.',
            'data' => $message,
        ]);
    }
}
