<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\MercadoPagoPaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class MercadoPagoWebhookController extends Controller
{
    public function handle(Request $request, MercadoPagoPaymentService $payments): Response
    {
        if (! filled(config('chatbox.mercadopago.access_token'))) {
            abort(503, 'Mercado Pago access token not configured.');
        }

        try {
            $payments->handleWebhook($request);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('mercadopago_webhook_handler_failed', [
                'type' => $request->input('type'),
                'message' => $e->getMessage(),
            ]);

            return response('Handler error', 500);
        }

        return response('OK', 200);
    }
}
