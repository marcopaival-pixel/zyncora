<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Cliente mínimo para a WhatsApp Cloud API (Meta Graph).
 *
 * @see https://developers.facebook.com/docs/whatsapp/cloud-api/guides/send-messages
 */
class WhatsAppCloudClient
{
    /**
     * @return array<string, mixed>
     */
    public function sendText(string $phoneNumberId, string $accessToken, string $toDigits, string $body): array
    {
        $version = (string) config('chatbox.whatsapp.graph_version', 'v21.0');
        $url = sprintf('https://graph.facebook.com/%s/%s/messages', $version, $phoneNumberId);

        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->asJson()
            ->timeout(30)
            ->post($url, [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $toDigits,
                'type' => 'text',
                'text' => [
                    'preview_url' => false,
                    'body' => $body,
                ],
            ]);

        if ($response->failed()) {
            throw new \RuntimeException(sprintf('WhatsApp API HTTP %s: %s', $response->status(), $response->body()));
        }

        /** @var array<string, mixed> */
        $json = $response->json();

        return $json;
    }

    /**
     * Envia mensagens interativas (botões ou listas).
     */
    public function sendInteractive(string $phoneNumberId, string $accessToken, string $toDigits, array $interactiveData): array
    {
        $version = (string) config('chatbox.whatsapp.graph_version', 'v21.0');
        $url = sprintf('https://graph.facebook.com/%s/%s/messages', $version, $phoneNumberId);

        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->asJson()
            ->timeout(30)
            ->post($url, [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $toDigits,
                'type' => 'interactive',
                'interactive' => $interactiveData,
            ]);

        if ($response->failed()) {
            throw new \RuntimeException(sprintf('WhatsApp API HTTP %s: %s', $response->status(), $response->body()));
        }

        /** @var array<string, mixed> */
        $json = $response->json();

        return $json;
    }
}
