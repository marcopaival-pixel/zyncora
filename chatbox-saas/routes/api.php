<?php

use App\Http\Controllers\Api\V1\HealthStatusController;
use App\Http\Controllers\Api\V1\AuthTokenController;
use App\Http\Controllers\Api\V1\ConversationController;
use App\Http\Controllers\Api\V1\MessageController;
use App\Http\Controllers\Api\V1\WhatsAppWebhookController;
use App\Http\Controllers\Api\V1\WidgetConfigController;
use App\Http\Controllers\Api\ApiDocsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api.docs', 'api.docs.auth', 'throttle:60,1'])->group(function () {
    Route::get('docs', [ApiDocsController::class, 'index'])->name('api.docs');
});

Route::prefix('v1')->group(function () {
    Route::get('openapi.yaml', [\App\Http\Controllers\Api\V1\OpenApiController::class, 'show'])
        ->middleware('throttle:60,1');

    Route::get('health/status', [HealthStatusController::class, 'show'])
        ->middleware(['health.token', 'throttle:30,1']);

    Route::prefix('widget')->group(function () {
        // Bootstrap Seguro (Injeção via Script Tag)
        Route::get('bootstrap/{token}', [\App\Http\Controllers\Api\WidgetBootstrapController::class, 'bootstrap']);
        
        // Rota de CSP para painel/admin
        Route::get('{chatbot}/csp-guidelines', [\App\Http\Controllers\Api\WidgetCspController::class, 'getGuidelines']);

        // As rotas abaixo exigem Middleware de Segurança (Tokens, Domínio, Fingerprint)
        Route::middleware('widget.access')->group(function () {
            Route::get('{slug}/config', [WidgetConfigController::class, 'show'])
                ->middleware('throttle:120,1');
            Route::post('{slug}/conversations', [ConversationController::class, 'startOrResume'])
                ->middleware('throttle:60,1');
            Route::get('{slug}/conversations/{conversation}/messages', [MessageController::class, 'index'])
                ->middleware('throttle:120,1');
            Route::post('{slug}/conversations/{conversation}/messages', [MessageController::class, 'store'])
                ->middleware('throttle:90,1');
        });
    });

    Route::post('auth/token', [AuthTokenController::class, 'store'])
        ->middleware('throttle:20,1');
    Route::post('auth/forgot-password', [\App\Http\Controllers\Api\V1\ForgotPasswordController::class, 'sendResetLink'])
        ->name('password.email')
        ->middleware('throttle:5,1');
    Route::post('auth/reset-password', [\App\Http\Controllers\Api\V1\ResetPasswordController::class, 'reset'])
        ->name('password.update')
        ->middleware('throttle:5,1');

    Route::get('integrations/whatsapp/webhook/universal', [WhatsAppWebhookController::class, 'universalVerify']);
    Route::post('integrations/whatsapp/webhook/universal', [WhatsAppWebhookController::class, 'universalIngest'])
        ->middleware('throttle:500,1');

    Route::get('integrations/whatsapp/webhook/{companySlug}', [WhatsAppWebhookController::class, 'verify']);
    Route::post('integrations/whatsapp/webhook/{companySlug}', [WhatsAppWebhookController::class, 'ingest'])
        ->middleware('throttle:300,1');

    Route::post('integrations/telegram/webhook/{companySlug}', [\App\Http\Controllers\Api\V1\TelegramWebhookController::class, 'ingest'])
        ->middleware('throttle:300,1');

    Route::get('integrations/instagram/webhook/universal', [\App\Http\Controllers\Api\V1\InstagramWebhookController::class, 'universalVerify']);
    Route::post('integrations/instagram/webhook/universal', [\App\Http\Controllers\Api\V1\InstagramWebhookController::class, 'universalIngest'])
        ->middleware('throttle:500,1');

    Route::get('integrations/messenger/webhook/universal', [\App\Http\Controllers\Api\V1\MessengerWebhookController::class, 'universalVerify']);
    Route::post('integrations/messenger/webhook/universal', [\App\Http\Controllers\Api\V1\MessengerWebhookController::class, 'universalIngest'])
        ->middleware('throttle:500,1');

    Route::post('payments/stripe/webhook', [\App\Http\Controllers\Api\V1\StripeWebhookController::class, 'handle'])
        ->middleware('throttle:120,1');

    Route::post('payments/mercadopago/webhook', [\App\Http\Controllers\Api\V1\MercadoPagoWebhookController::class, 'handle'])
        ->middleware('throttle:120,1');
});

Route::middleware(['auth:sanctum', 'throttle:120,1'])->prefix('v1')->group(function () {
    Route::get('conversations', [ConversationController::class, 'index']);
    Route::get('conversations/{conversation}', [ConversationController::class, 'show']);
    Route::post('conversations/{conversation}/messages', [MessageController::class, 'storeAgent']);
});

// API Pública Oficial (Para clientes criarem automações via n8n, zapier, etc)
Route::middleware(['auth:sanctum', 'throttle:300,1'])->prefix('public/v1')->group(function () {
    Route::get('conversations', [\App\Http\Controllers\Api\PublicV1\PublicConversationController::class, 'index']);
    Route::get('conversations/{conversation}', [\App\Http\Controllers\Api\PublicV1\PublicConversationController::class, 'show']);
    Route::post('messages/send', [\App\Http\Controllers\Api\PublicV1\PublicMessageController::class, 'send']);
});

use App\Http\Controllers\DemoChatController;

Route::middleware(['demo.enabled', 'throttle:30,1'])->group(function () {
    Route::post('demo-chat', [DemoChatController::class, 'chat'])->name('api.demo.chat');
    Route::post('demo-lead', [DemoChatController::class, 'captureLead'])->name('api.demo.lead');
});
Route::get('/plans', [\App\Http\Controllers\Api\PlanApiController::class, 'index']);
