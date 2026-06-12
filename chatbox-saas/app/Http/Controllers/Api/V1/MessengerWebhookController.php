<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\WebhookIngestionService;
use App\Jobs\IngestWebhookPayloadJob;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MessengerWebhookController extends Controller
{
    public function __construct(
        protected WebhookIngestionService $ingestionService
    ) {}

    /**
     * Verificação Universal da Graph API do Facebook Messenger (SaaS Central).
     */
    public function universalVerify(Request $request): Response
    {
        $expected = config('chatbox.messenger.universal_verify_token', 'zincora_messenger_token');
        return $this->ingestionService->universalVerify($request, $expected);
    }

    /**
     * Ingestão Universal de Mensagens do Messenger.
     */
    public function universalIngest(Request $request): Response
    {
        $appSecret = config('chatbox.messenger.universal_app_secret');
        if (! $this->ingestionService->validateUniversalSignature($request, $appSecret, 'messenger')) {
            abort(403, 'Invalid signature.');
        }

        $payload = $request->all();
        $object = data_get($payload, 'object');

        if ($object !== 'page') {
            return response('EVENT_RECEIVED', 200);
        }

        $entries = data_get($payload, 'entry', []);

        foreach ($entries as $entry) {
            $pageId = data_get($entry, 'id');
            $messagings = data_get($entry, 'messaging', []);

            if (!$pageId) {
                continue;
            }

            foreach ($messagings as $msgEvent) {
                $senderId = (string) data_get($msgEvent, 'sender.id');
                $text = (string) data_get($msgEvent, 'message.text', '');
                $isEcho = data_get($msgEvent, 'message.is_echo', false);

                if ($isEcho || $senderId === '' || $text === '') {
                    continue;
                }

                IngestWebhookPayloadJob::dispatch(
                    'messenger',
                    $pageId,
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
