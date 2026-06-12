<?php

namespace App\Http\Controllers\Api\PublicV1;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicConversationController extends Controller
{
    /**
     * Lista as conversas ativas da empresa autenticada via API Token.
     */
    public function index(Request $request): JsonResponse
    {
        $companyId = $request->user()->company_id;

        $conversations = Conversation::query()
            ->where('company_id', $companyId)
            ->with(['channel'])
            ->orderByDesc('updated_at')
            ->paginate($request->query('per_page', 50));

        return response()->json($conversations);
    }

    /**
     * Retorna os detalhes de uma conversa específica.
     */
    public function show(Request $request, Conversation $conversation): JsonResponse
    {
        if ($conversation->company_id !== $request->user()->company_id) {
            abort(403, 'Unauthorized access to this conversation.');
        }

        $conversation->load(['messages' => function ($query) {
            $query->orderBy('sent_at', 'desc')->limit(50);
        }, 'channel']);

        return response()->json($conversation);
    }
}
