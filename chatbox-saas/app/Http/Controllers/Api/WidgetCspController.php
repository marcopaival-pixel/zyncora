<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chatbot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WidgetCspController extends Controller
{
    /**
     * Retorna a política de Content Security Policy (CSP) recomendada para o cliente.
     */
    public function getGuidelines(Request $request, Chatbot $chatbot): JsonResponse
    {
        // Neste endpoint, assumimos que o cliente/admin está buscando as instruções.
        // O domínio base da Zynkora de onde saem os assets e a API.
        $appDomain = config('app.url', 'https://app.zynkora.com');
        $wsDomain = config('broadcasting.connections.pusher.options.host', 'ws.zynkora.com');

        $cspPolicy = sprintf(
            "default-src 'self'; script-src 'self' 'unsafe-inline' %s; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; connect-src 'self' wss://%s https://%s %s; img-src 'self' data: https:;",
            $appDomain,
            $wsDomain,
            $wsDomain,
            $appDomain
        );

        return response()->json([
            'status' => 'success',
            'data' => [
                'meta_tag' => sprintf('<meta http-equiv="Content-Security-Policy" content="%s">', $cspPolicy),
                'header_example' => sprintf('Content-Security-Policy: %s', $cspPolicy),
                'instructions' => 'Cole a meta tag dentro da seção <head> do seu site para garantir que apenas scripts autorizados da Zynkora possam interagir com o Widget, bloqueando ataques XSS.',
            ],
        ]);
    }
}
