<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Jobs\IngestWebhookPayloadJob;
use App\Models\Company;
use App\Services\WebhookIngestionService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WhatsAppWebhookController extends Controller
{
    public function __construct(
        protected WebhookIngestionService $ingestionService
    ) {}

    /**
     * Meta WhatsApp Cloud API — webhook verification (challenge).
     */
    public function verify(Request $request, string $companySlug): Response
    {
        $company = Company::query()->where('slug', $companySlug)->firstOrFail();

        return $this->ingestionService->companyVerify($request, $company, 'whatsapp_cloud');
    }

    /**
     * Webhook Universal (Para a Meta App Central do SaaS).
     */
    public function universalVerify(Request $request): Response
    {
        $expected = config('chatbox.whatsapp.universal_verify_token', 'zincora_universal_token');

        return $this->ingestionService->universalVerify($request, $expected);
    }

    /**
     * Inbound events — estrutura simplificada Cloud API (mensagens texto).
     */
    public function ingest(Request $request, string $companySlug): Response
    {
        $company = Company::query()->where('slug', $companySlug)->firstOrFail();

        // 1. Validar Assinatura
        if (! $this->ingestionService->validateCompanySignature($request, $company, 'whatsapp_cloud')) {
            abort(403, 'Invalid signature.');
        }

        $payload = $request->all();
        $entries = data_get($payload, 'entry', []);

        foreach ($entries as $entry) {
            $changes = data_get($entry, 'changes', []);
            foreach ($changes as $change) {
                $messages = data_get($change, 'value.messages', []);
                $metadata = data_get($change, 'value.metadata', []);
                $phoneNumberId = data_get($metadata, 'phone_number_id');

                foreach ($messages as $msg) {
                    $from = (string) data_get($msg, 'from');
                    $type = (string) data_get($msg, 'type', '');

                    if (! in_array($type, ['text', 'image', 'audio', 'video', 'document']) || $from === '') {
                        continue;
                    }

                    IngestWebhookPayloadJob::dispatch(
                        'whatsapp',
                        $phoneNumberId ?: 'default',
                        $from,
                        $msg,
                        $type,
                        $company->id
                    );
                }
            }
        }

        return response('EVENT_RECEIVED', 200);
    }

    /**
     * Ingestão Universal. Mapeia a empresa através do phone_number_id.
     */
    public function universalIngest(Request $request): Response
    {
        $appSecret = config('chatbox.whatsapp.universal_app_secret');
        if (! $this->ingestionService->validateUniversalSignature($request, $appSecret, 'whatsapp')) {
            abort(403, 'Invalid signature.');
        }

        $payload = $request->all();
        $entries = data_get($payload, 'entry', []);

        foreach ($entries as $entry) {
            $changes = data_get($entry, 'changes', []);
            foreach ($changes as $change) {
                $messages = data_get($change, 'value.messages', []);
                $metadata = data_get($change, 'value.metadata', []);
                $phoneNumberId = data_get($metadata, 'phone_number_id');

                if (! $phoneNumberId) {
                    continue;
                }

                foreach ($messages as $msg) {
                    $from = (string) data_get($msg, 'from');
                    $type = (string) data_get($msg, 'type', '');

                    if (! in_array($type, ['text', 'image', 'audio', 'video', 'document']) || $from === '') {
                        continue;
                    }

                    IngestWebhookPayloadJob::dispatch(
                        'whatsapp',
                        $phoneNumberId,
                        $from,
                        $msg,
                        $type,
                        null
                    );
                }
            }
        }

        return response('EVENT_RECEIVED', 200);
    }
}
