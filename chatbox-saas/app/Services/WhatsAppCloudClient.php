<?php

namespace App\Services;

use App\Models\SystemErrorLog;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
            ->retry(3, 1000, function (\Exception $exception, $request) {
                // Tenta novamente caso seja erro de servidor ou rate limit (429)
                return $exception instanceof RequestException &&
                       ($exception->response->status() >= 500 || $exception->response->status() === 429);
            })
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
            $errorMessage = sprintf('WhatsApp API HTTP %s: %s', $response->status(), $response->body());
            Log::error($errorMessage);

            SystemErrorLog::create([
                'category' => 'whatsapp_api',
                'error_message' => $errorMessage,
                'context' => [
                    'phone_number_id' => $phoneNumberId,
                    'to' => $toDigits,
                    'type' => 'text',
                    'status_code' => $response->status(),
                ],
                'severity' => $response->status() === 429 ? 'high' : 'medium',
                'occurred_at' => now(),
            ]);

            throw new \RuntimeException($errorMessage);
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
            ->retry(3, 1000, function (\Exception $exception, $request) {
                return $exception instanceof RequestException &&
                       ($exception->response->status() >= 500 || $exception->response->status() === 429);
            })
            ->post($url, [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $toDigits,
                'type' => 'interactive',
                'interactive' => $interactiveData,
            ]);

        if ($response->failed()) {
            $errorMessage = sprintf('WhatsApp API HTTP %s: %s', $response->status(), $response->body());
            Log::error($errorMessage);

            SystemErrorLog::create([
                'category' => 'whatsapp_api',
                'error_message' => $errorMessage,
                'context' => [
                    'phone_number_id' => $phoneNumberId,
                    'to' => $toDigits,
                    'type' => 'interactive',
                    'status_code' => $response->status(),
                ],
                'severity' => $response->status() === 429 ? 'high' : 'medium',
                'occurred_at' => now(),
            ]);

            throw new \RuntimeException($errorMessage);
        }

        /** @var array<string, mixed> */
        $json = $response->json();

        return $json;
    }
}
