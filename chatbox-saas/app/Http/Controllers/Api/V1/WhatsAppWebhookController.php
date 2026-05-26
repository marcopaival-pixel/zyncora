<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\Company;
use App\Models\CompanyIntegration;
use App\Models\Conversation;
use App\Services\ChatbotReplyService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

use App\Jobs\ProcessWebhookMessage;

class WhatsAppWebhookController extends Controller
{
    public function __construct() {}

    /**
     * Meta WhatsApp Cloud API — webhook verification (challenge).
     */
    public function verify(Request $request, string $companySlug): Response
    {
        $mode = $request->query('hub_mode') ?? $request->query('hub.mode');
        $token = $request->query('hub_verify_token') ?? $request->query('hub.verify_token');
        $challenge = $request->query('hub_challenge') ?? $request->query('hub.challenge');

        $company = Company::query()->where('slug', $companySlug)->firstOrFail();
        $integration = CompanyIntegration::query()
            ->where('company_id', $company->id)
            ->where('driver', 'whatsapp_cloud')
            ->first();

        $expected = $integration?->webhook_verify_token ?? '';

        if ($mode === 'subscribe' && $expected !== '' && hash_equals($expected, (string) $token)) {
            return response((string) $challenge, 200);
        }

        abort(403, 'Verification failed.');
    }

    /**
     * Inbound events — estrutura simplificada Cloud API (mensagens texto).
     */
    public function ingest(Request $request, string $companySlug): Response
    {
        $company = Company::query()->where('slug', $companySlug)->firstOrFail();
        
        // 1. Validar Assinatura (Segurança Crítica)
        if (! $this->validateSignature($request, $company)) {
            Log::warning('whatsapp_webhook_invalid_signature', [
                'company' => $companySlug,
                'ip' => $request->ip(),
            ]);
            abort(403, 'Invalid signature.');
        }

        $payload = $request->all();

        Log::info('whatsapp_webhook', [
            'company' => $companySlug,
            'keys' => array_keys($payload),
        ]);

        $entries = data_get($payload, 'entry', []);
        foreach ($entries as $entry) {
            $changes = data_get($entry, 'changes', []);
            foreach ($changes as $change) {
                $messages = data_get($change, 'value.messages', []);
                $metadata = data_get($change, 'value.metadata', []);
                $phoneNumberId = data_get($metadata, 'phone_number_id');

                foreach ($messages as $msg) {
                    $from = (string) data_get($msg, 'from');
                    $text = (string) data_get($msg, 'text.body', '');
                    $type = (string) data_get($msg, 'type', '');

                    if ($type !== 'text' || $from === '' || $text === '') {
                        continue;
                    }

                    $this->recordInboundCompanyMessage($company, $phoneNumberId, $from, $text);
                }
            }
        }

        return response('EVENT_RECEIVED', 200);
    }

    protected function recordInboundCompanyMessage(
        Company $company,
        ?string $phoneNumberId,
        string $from,
        string $text
    ): void {
        $lockKey = "whatsapp_inbound_lock_{$company->id}_{$from}";

        \Illuminate\Support\Facades\Cache::lock($lockKey, 10)->block(5, function () use ($company, $phoneNumberId, $from, $text) {
            $channel = Channel::query()->firstOrCreate(
                [
                    'company_id' => $company->id,
                    'type' => 'whatsapp',
                    'external_ref' => $phoneNumberId ?: 'default',
                ],
                ['status' => 'active']
            );

            $conversation = Conversation::query()
                ->where('company_id', $company->id)
                ->where('channel_id', $channel->id)
                ->where('client_phone', $from)
                ->where('status', '!=', 'closed')
                ->orderByDesc('id')
                ->first();

            if (! $conversation) {
                $conversation = Conversation::query()->create([
                    'company_id' => $company->id,
                    'channel_id' => $channel->id,
                    'visitor_token' => bin2hex(random_bytes(16)),
                    'client_phone' => $from,
                    'status' => 'open',
                    'started_at' => now(),
                ]);
            }

            $conversation->messages()->create([
                'sender_type' => 'visitor',
                'body' => $text,
                'message_type' => 'text',
                'meta' => ['source' => 'whatsapp_cloud'],
                'sent_at' => now(),
            ]);

            // 3. Processar Resposta de Chatbot Assincronamente (Queue)
            ProcessWebhookMessage::dispatch($conversation, $text);
        });
    }

    protected function validateSignature(Request $request, Company $company): bool
    {
        $signature = $request->header('X-Hub-Signature-256');
        if (! $signature) {
            return false;
        }

        $integration = CompanyIntegration::query()
            ->where('company_id', $company->id)
            ->where('driver', 'whatsapp_cloud')
            ->first();

        // Se o App Secret não estiver configurado, podemos logar um aviso ou permitir (ajuste conforme rigor)
        $appSecret = data_get($integration?->credentials, 'app_secret');
        if (! $appSecret) {
            Log::warning('whatsapp_webhook_missing_app_secret', [
                'company_id' => $company->id,
            ]);

            return false;
        }

        $expected = 'sha256=' . hash_hmac('sha256', $request->getContent(), (string) $appSecret);

        return hash_equals($expected, (string) $signature);
    }
}
