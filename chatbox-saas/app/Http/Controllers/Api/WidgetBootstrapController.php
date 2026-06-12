<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\WidgetSecurityService;
use Illuminate\Support\Str;

class WidgetBootstrapController extends Controller
{
    protected WidgetSecurityService $securityService;

    public function __construct(WidgetSecurityService $securityService)
    {
        $this->securityService = $securityService;
    }

    /**
     * Endpoint inicial para carregar as configurações do widget.
     * Retorna um JWT de sessão de curta duração para chamadas subsequentes.
     */
    public function bootstrap(Request $request, $token)
    {
        // O Middleware ValidateWidgetAccess já inseriu o chatbot
        $chatbot = $request->attributes->get('chatbot');
        $domain = $request->header('Origin') ?? $request->header('Referer') ?? 'unknown';
        $sessionId = $request->header('X-Widget-Session-Id') ?? Str::uuid()->toString();

        // Gerar JWT Temporário
        $sessionJwt = $this->securityService->generateSessionJwt($chatbot, $domain, $sessionId);

        // Determinar White Label (Planos Enterprise)
        $hasWhiteLabel = $chatbot->company->plan->has_white_label ?? false;

        // Payload de Configuração Segura (não expõe secrets)
        $config = [
            'id' => $chatbot->id,
            'name' => $chatbot->name,
            'initial_message' => $chatbot->initial_message,
            'theme' => [
                'color' => $chatbot->color ?? '#000000',
                'logo' => $chatbot->logo_url,
            ],
            'jwt' => $sessionJwt,
            'session_id' => $sessionId,
            'white_label' => $hasWhiteLabel,
            // Outras configurações visuais não-sensíveis...
        ];

        return response()->json($config)
            ->header('Cache-Control', 'public, max-age=300'); // Cache Edge 5 mins para sucesso
    }
}
