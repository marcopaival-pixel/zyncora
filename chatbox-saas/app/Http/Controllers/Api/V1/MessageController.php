<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\ChatbotReplyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct(
        protected ChatbotReplyService $chatbots
    ) {}

    public function index(Request $request, string $slug, Conversation $conversation): JsonResponse
    {
        $company = Company::query()->where('slug', $slug)->where('status', 'active')->firstOrFail();
        if ((int) $conversation->company_id !== (int) $company->id) {
            abort(404);
        }

        $this->assertVisitor($request, $conversation);

        $validated = $request->validate([
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

        $perPage = $validated['per_page'] ?? 50;

        $paginator = $conversation->messages()
            ->orderBy('id')
            ->paginate($perPage);

        return response()->json($paginator);
    }

    public function store(Request $request, string $slug, Conversation $conversation): JsonResponse
    {
        $company = Company::query()->where('slug', $slug)->where('status', 'active')->firstOrFail();
        if ((int) $conversation->company_id !== (int) $company->id) {
            abort(404);
        }

        $this->assertVisitor($request, $conversation);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:8000'],
            'message_type' => ['nullable', 'string', 'max:32'],
        ]);

        $visitor = $conversation->messages()->create([
            'sender_type' => 'visitor',
            'sender_id' => null,
            'body' => $data['body'],
            'message_type' => $data['message_type'] ?? 'text',
            'sent_at' => now(),
        ]);

        $bot = $this->chatbots->maybeAutoReply($conversation, $data['body']);

        return response()->json([
            'visitor_message' => $visitor,
            'bot_message' => $bot,
        ]);
    }

    public function storeAgent(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorize('update', $conversation);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:8000'],
            'message_type' => ['nullable', 'string', 'max:32'],
        ]);

        $message = $conversation->messages()->create([
            'sender_type' => 'agent',
            'sender_id' => $request->user()->id,
            'body' => $data['body'],
            'message_type' => $data['message_type'] ?? 'text',
            'sent_at' => now(),
        ]);

        return response()->json($message);
    }

    protected function assertVisitor(Request $request, Conversation $conversation): void
    {
        $token = $request->header('X-Visitor-Token');
        if (! $token || ! hash_equals((string) $conversation->visitor_token, (string) $token)) {
            abort(403, 'Invalid visitor token.');
        }
    }
}
