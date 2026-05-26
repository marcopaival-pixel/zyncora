<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StartConversationRequest;
use App\Models\Channel;
use App\Models\Chatbot;
use App\Models\Company;
use App\Models\Conversation;
use App\Services\ChatbotReplyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function __construct(
        protected ChatbotReplyService $chatbots
    ) {}

    public function startOrResume(StartConversationRequest $request, string $slug): JsonResponse
    {
        $company = Company::query()->where('slug', $slug)->where('status', 'active')->firstOrFail();

        $data = $request->validated();

        if (! empty($data['visitor_token'])) {
            $conversation = Conversation::query()
                ->where('company_id', $company->id)
                ->where('visitor_token', $data['visitor_token'])
                ->where('status', '!=', 'closed')
                ->first();

            if ($conversation) {
                return response()->json($this->shapeConversation($conversation));
            }
        }

        $channel = Channel::query()->firstOrCreate(
            [
                'company_id' => $company->id,
                'type' => 'site',
            ],
            [
                'status' => 'active',
            ]
        );

        $conversation = Conversation::query()->create([
            'company_id' => $company->id,
            'channel_id' => $channel->id,
            'visitor_token' => bin2hex(random_bytes(16)),
            'client_name' => $data['client_name'] ?? null,
            'client_phone' => $data['client_phone'] ?? null,
            'client_email' => $data['client_email'] ?? null,
            'status' => 'open',
            'started_at' => now(),
        ]);

        $offline = $this->chatbots->offlineNoticeIfClosed($company);
        if ($offline) {
            $conversation->messages()->create([
                'sender_type' => 'bot',
                'body' => $offline,
                'message_type' => 'text',
                'sent_at' => now(),
            ]);
        } else {
            $welcome = $company->welcome_message ?: 'Olá! Como podemos ajudar?';
            $conversation->loadMissing('channel');
            $activeBot = Chatbot::resolveForConversation($conversation);
            if ($activeBot && $activeBot->initial_message) {
                $welcome = $activeBot->initial_message;
            }
            $conversation->messages()->create([
                'sender_type' => 'bot',
                'body' => $welcome,
                'message_type' => 'text',
                'sent_at' => now(),
            ]);
        }

        return response()->json($this->shapeConversation($conversation));
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Conversation::class);

        $user = $request->user();
        $query = Conversation::query()
            ->with(['assignee', 'channel'])
            ->orderByDesc('updated_at');

        if (! $user->isPlatformAdmin()) {
            $query->where('company_id', $user->company_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }

        $perPage = min((int) $request->query('per_page', 25), 100);

        return response()->json($query->paginate($perPage));
    }

    public function show(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorize('view', $conversation);

        $request->validate([
            'messages_limit' => ['sometimes', 'integer', 'min:1', 'max:500'],
            'messages_after' => ['sometimes', 'integer', 'min:1'],
        ]);

        $limit = min(max((int) $request->query('messages_limit', 150), 1), 500);
        $afterId = $request->query('messages_after');

        $conversation->load(['assignee', 'channel']);

        $messagesQuery = $conversation->messages()->orderBy('id');
        if ($afterId !== null && $afterId !== '') {
            $messagesQuery->where('id', '>', (int) $afterId);
        }
        $messages = $messagesQuery->limit($limit)->get();
        $conversation->setRelation('messages', $messages);

        return response()->json($conversation);
    }

    protected function shapeConversation(Conversation $conversation): array
    {
        return [
            'id' => $conversation->id,
            'visitor_token' => $conversation->visitor_token,
            'status' => $conversation->status,
        ];
    }
}
