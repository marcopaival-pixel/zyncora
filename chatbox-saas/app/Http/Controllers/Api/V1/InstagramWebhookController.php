<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\WebhookIngestionService;
use App\Jobs\IngestWebhookPayloadJob;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InstagramWebhookController extends Controller
{
    public function __construct(
        protected WebhookIngestionService $ingestionService
    ) {}

    /**
     * Verificação Universal da Graph API do Instagram (SaaS Central).
     */
    public function universalVerify(Request $request): Response
    {
        $expected = config('chatbox.instagram.universal_verify_token', 'zincora_ig_token');
        return $this->ingestionService->universalVerify($request, $expected);
    }

    /**
     * Ingestão Universal de Mensagens do Instagram Direct.
     */
    public function universalIngest(Request $request): Response
    {
        $appSecret = config('chatbox.instagram.universal_app_secret');
        if (! $this->ingestionService->validateUniversalSignature($request, $appSecret, 'instagram')) {
            abort(403, 'Invalid signature.');
        }

        $payload = $request->all();
        $object = data_get($payload, 'object');

        if ($object !== 'instagram') {
            return response('EVENT_RECEIVED', 200); // Ignore non-instagram objects
        }

        $entries = data_get($payload, 'entry', []);

        foreach ($entries as $entry) {
            $igAccountId = data_get($entry, 'id'); // The business Instagram Account ID receiving the message
            $messagings = data_get($entry, 'messaging', []);

            if (!$igAccountId) {
                continue;
            }

            foreach ($messagings as $msgEvent) {
                $senderId = (string) data_get($msgEvent, 'sender.id');
                $text = (string) data_get($msgEvent, 'message.text', '');
                $isEcho = data_get($msgEvent, 'message.is_echo', false);

                // Evitar processar mensagens enviadas pela própria página (echos) ou mensagens vazias
                if ($isEcho || $senderId === '' || $text === '') {
                    continue;
                }

                IngestWebhookPayloadJob::dispatch(
                    'instagram',
                    $igAccountId,
                    $senderId,
                    ['text' => $text],
                    'text',
                    null
                );
            }
        }

        return response('EVENT_RECEIVED', 200);
    }
}
