<?php

namespace App\Services;

use App\Models\CompanyIntegration;
use App\Models\Message;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WhatsAppMediaService
{
    /**
     * Faz o download de uma mídia da Graph API do WhatsApp e salva no storage.
     * Retorna a URL pública ou caminho armazenado.
     */
    public function downloadAndStoreMedia(string $mediaId, string $mimeType, Message $message): ?string
    {
        try {
            $company = $message->conversation->company;
            if (! $company) {
                return null;
            }

            $integration = CompanyIntegration::query()
                ->where('company_id', $company->id)
                ->where('driver', 'whatsapp_cloud')
                ->first();

            $accessToken = $integration?->credentials['access_token'] ?? config('chatbox.whatsapp.universal_access_token');

            if (! $accessToken) {
                Log::warning('whatsapp_media_download_failed', ['reason' => 'missing_access_token', 'media_id' => $mediaId]);

                return null;
            }

            $version = config('chatbox.whatsapp.graph_version', 'v21.0');

            // 1. Obter a URL da mídia
            $urlResponse = Http::withToken($accessToken)
                ->acceptJson()
                ->get("https://graph.facebook.com/{$version}/{$mediaId}");

            if ($urlResponse->failed()) {
                Log::error('whatsapp_media_url_failed', ['media_id' => $mediaId, 'error' => $urlResponse->body()]);

                return null;
            }

            $mediaUrl = $urlResponse->json('url');
            if (! $mediaUrl) {
                return null;
            }

            // 2. Fazer o download binário do arquivo
            $fileResponse = Http::withToken($accessToken)->get($mediaUrl);
            if ($fileResponse->failed()) {
                Log::error('whatsapp_media_download_failed', ['media_url' => $mediaUrl, 'error' => $fileResponse->body()]);

                return null;
            }

            // 3. Determinar extensão
            $extension = $this->getExtensionFromMime($mimeType);
            $filename = Str::uuid().'.'.$extension;
            $path = "whatsapp_media/{$company->id}/".date('Y/m')."/{$filename}";

            // 4. Salvar no Storage (Local ou S3)
            Storage::disk('public')->put($path, $fileResponse->body());

            return $path;

        } catch (\Exception $e) {
            Log::error('whatsapp_media_exception', ['message' => $e->getMessage()]);

            return null;
        }
    }

    private function getExtensionFromMime(string $mimeType): string
    {
        $map = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'audio/ogg' => 'ogg',
            'audio/mp4' => 'm4a',
            'video/mp4' => 'mp4',
            'application/pdf' => 'pdf',
        ];

        return $map[$mimeType] ?? 'bin';
    }
}
