<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\StripePaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function handle(Request $request, StripePaymentService $payments): Response
    {
        $secret = config('chatbox.stripe.webhook_secret');

        if (! filled($secret)) {
            abort(503, 'Stripe webhook secret not configured.');
        }

        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent($payload, (string) $signature, $secret);
        } catch (SignatureVerificationException $e) {
            Log::warning('stripe_webhook_invalid_signature', ['message' => $e->getMessage()]);

            abort(403, 'Invalid signature.');
        }

        try {
            $payments->handleWebhookEvent($event);
        } catch (\Throwable $e) {
            Log::error('stripe_webhook_handler_failed', [
                'type' => $event->type ?? null,
                'message' => $e->getMessage(),
            ]);

            return response('Handler error', 500);
        }

        return response('OK', 200);
    }
}
