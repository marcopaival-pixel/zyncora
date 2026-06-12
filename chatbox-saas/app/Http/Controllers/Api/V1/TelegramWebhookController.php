<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Jobs\IngestWebhookPayloadJob;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TelegramWebhookController extends Controller
{
    /**
     * Webhook de entrada para o Telegram (por slug da empresa).
     */
    public function ingest(Request $request, string $companySlug): Response
    {
        $company = Company::query()->where('slug', $companySlug)->firstOrFail();

        $payload = $request->all();

        // Extrai a mensagem
        $messageData = data_get($payload, 'message', []);
        $chatId = (string) data_get($messageData, 'chat.id');
        $text = (string) data_get($messageData, 'text', '');

        if ($chatId === '' || $text === '') {
            return response('OK', 200);
        }

        IngestWebhookPayloadJob::dispatch(
            'telegram',
            'default', // Telegram geralmente tem um bot por empresa
            $chatId,
            ['text' => $text],
            'text',
            $company->id
        );

        return response('OK', 200);
    }
}
