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

    Route::get('widget/{slug}/config', [WidgetConfigController::class, 'show'])
        ->middleware('throttle:120,1');

    Route::post('widget/{slug}/conversations', [ConversationController::class, 'startOrResume'])
        ->middleware('throttle:60,1');
    Route::get('widget/{slug}/conversations/{conversation}/messages', [MessageController::class, 'index'])
        ->middleware('throttle:120,1');
    Route::post('widget/{slug}/conversations/{conversation}/messages', [MessageController::class, 'store'])
        ->middleware('throttle:90,1');

    Route::post('auth/token', [AuthTokenController::class, 'store'])
        ->middleware('throttle:20,1');
    Route::post('auth/forgot-password', [\App\Http\Controllers\Api\V1\ForgotPasswordController::class, 'sendResetLink'])
        ->name('password.email')
        ->middleware('throttle:5,1');
    Route::post('auth/reset-password', [\App\Http\Controllers\Api\V1\ResetPasswordController::class, 'reset'])
        ->name('password.update')
        ->middleware('throttle:5,1');

    Route::get('integrations/whatsapp/webhook/{companySlug}', [WhatsAppWebhookController::class, 'verify']);
    Route::post('integrations/whatsapp/webhook/{companySlug}', [WhatsAppWebhookController::class, 'ingest'])
        ->middleware('throttle:300,1');

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

use App\Http\Controllers\DemoChatController;

Route::middleware(['demo.enabled', 'throttle:30,1'])->group(function () {
    Route::post('demo-chat', [DemoChatController::class, 'chat'])->name('api.demo.chat');
    Route::post('demo-lead', [DemoChatController::class, 'captureLead'])->name('api.demo.lead');
});
