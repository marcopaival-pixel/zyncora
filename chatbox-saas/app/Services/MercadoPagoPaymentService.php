<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class MercadoPagoPaymentService
{
    public function __construct(
        protected PlanSubscriptionService $subscriptions
    ) {}

    public function isConfigured(): bool
    {
        return filled(config('chatbox.mercadopago.access_token'));
    }

    public function createSubscriptionCheckout(Company $company, Plan $plan, User $user): string
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Mercado Pago não configurado (MERCADOPAGO_ACCESS_TOKEN em falta).');
        }

        $frequencyType = $plan->interval === 'year' ? 'years' : 'months';

        $response = Http::withToken((string) config('chatbox.mercadopago.access_token'))
            ->acceptJson()
            ->post($this->apiUrl('/preapproval'), [
                'reason' => 'Chatbox SaaS — '.$plan->name,
                'external_reference' => $this->buildExternalReference($company, $plan),
                'payer_email' => $user->email,
                'auto_recurring' => [
                    'frequency' => 1,
                    'frequency_type' => $frequencyType,
                    'transaction_amount' => (float) $plan->price,
                    'currency_id' => strtoupper((string) config('chatbox.mercadopago.currency', 'BRL')),
                ],
                'back_url' => url('/admin/billing').'?checkout=success',
                'status' => 'pending',
            ]);

        if (! $response->successful()) {
            $message = $response->json('message') ?? $response->body();

            throw new RuntimeException('Mercado Pago: '.$message);
        }

        $initPoint = $response->json('init_point') ?? $response->json('sandbox_init_point');

        if (! filled($initPoint)) {
            throw new RuntimeException('Mercado Pago não devolveu URL de checkout.');
        }

        return (string) $initPoint;
    }

    public function handleWebhook(Request $request): void
    {
        if (! $this->isValidWebhookSignature($request)) {
            abort(403, 'Invalid Mercado Pago signature.');
        }

        $type = (string) $request->input('type', '');
        $resourceId = (string) ($request->input('data.id') ?? $request->query('data.id', ''));

        if ($resourceId === '') {
            return;
        }

        if ($type === 'subscription_preapproval') {
            $this->processPreapproval($resourceId);

            return;
        }

        if ($type === 'payment') {
            $this->processPayment($resourceId);
        }
    }

    public function processPreapproval(string $preapprovalId): void
    {
        $preapproval = $this->fetchPreapproval($preapprovalId);

        if ($preapproval === null) {
            return;
        }

        $parsed = $this->parseExternalReference((string) ($preapproval['external_reference'] ?? ''));

        if ($parsed === null) {
            return;
        }

        $company = Company::query()->find($parsed['company_id']);

        if ($company === null) {
            return;
        }

        $company->update(['mercadopago_preapproval_id' => $preapprovalId]);

        $status = (string) ($preapproval['status'] ?? '');

        if ($status === 'authorized') {
            $plan = Plan::query()->find($parsed['plan_id']);

            if ($plan === null) {
                return;
            }

            $this->subscriptions->applyPlanToCompany($company, $plan);

            return;
        }

        if (in_array($status, ['cancelled', 'paused'], true)) {
            $this->subscriptions->markSubscriptionCanceled($company);
        }
    }

    public function processPayment(string $paymentId): void
    {
        $payment = $this->fetchPayment($paymentId);

        if ($payment === null) {
            return;
        }

        if ((string) ($payment['status'] ?? '') !== 'approved') {
            return;
        }

        $preapprovalId = (string) ($payment['preapproval_id'] ?? '');

        if ($preapprovalId !== '') {
            $company = $this->subscriptions->findCompanyByMercadoPagoPreapprovalId($preapprovalId);

            if ($company !== null) {
                $this->subscriptions->renewCompanySubscription($company);
                $this->createPaymentHistoryAndDispatch($company, $payment, 'subscription');

                return;
            }
        }

        $parsed = $this->parseExternalReference((string) ($payment['external_reference'] ?? ''));

        if ($parsed === null) {
            return;
        }

        $company = Company::query()->find($parsed['company_id']);

        if ($company === null) {
            return;
        }

        $this->subscriptions->renewCompanySubscription($company);
        $this->createPaymentHistoryAndDispatch($company, $payment, 'subscription');
    }

    protected function createPaymentHistoryAndDispatch(Company $company, array $payment, string $type): void
    {
        $amount = (float) ($payment['transaction_amount'] ?? 0);

        if ($amount <= 0) return;

        $paymentHistory = \App\Models\PaymentHistory::create([
            'company_id' => $company->id,
            'type' => $type,
            'amount' => $amount,
            'status' => 'paid',
            'gateway' => 'mercadopago',
            'external_id' => (string) ($payment['id'] ?? ''),
            'paid_at' => now(),
        ]);

        event(new \App\Events\PaymentApproved($paymentHistory));
    }

    public function buildExternalReference(Company $company, Plan $plan): string
    {
        return "company:{$company->id}:plan:{$plan->id}";
    }

    public function parseExternalReference(string $reference): ?array
    {
        if (preg_match('/company:(\d+):plan:(\d+)/', $reference, $matches) !== 1) {
            return null;
        }

        return [
            'company_id' => (int) $matches[1],
            'plan_id' => (int) $matches[2],
        ];
    }

    protected function fetchPreapproval(string $preapprovalId): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        $response = Http::withToken((string) config('chatbox.mercadopago.access_token'))
            ->acceptJson()
            ->get($this->apiUrl('/preapproval/'.$preapprovalId));

        if (! $response->successful()) {
            Log::warning('mercadopago_preapproval_fetch_failed', [
                'id' => $preapprovalId,
                'status' => $response->status(),
            ]);

            return null;
        }

        return $response->json();
    }

    protected function fetchPayment(string $paymentId): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        $response = Http::withToken((string) config('chatbox.mercadopago.access_token'))
            ->acceptJson()
            ->get($this->apiUrl('/v1/payments/'.$paymentId));

        if (! $response->successful()) {
            Log::warning('mercadopago_payment_fetch_failed', [
                'id' => $paymentId,
                'status' => $response->status(),
            ]);

            return null;
        }

        return $response->json();
    }

    protected function isValidWebhookSignature(Request $request): bool
    {
        $secret = config('chatbox.mercadopago.webhook_secret');

        if (! filled($secret)) {
            return app()->environment('local', 'testing');
        }

        $signatureHeader = (string) $request->header('x-signature', '');
        $requestId = (string) $request->header('x-request-id', '');

        if ($signatureHeader === '' || $requestId === '') {
            return false;
        }

        $ts = null;
        $v1 = null;

        foreach (explode(',', $signatureHeader) as $part) {
            [$key, $value] = array_pad(explode('=', trim($part), 2), 2, null);
            if ($key === 'ts') {
                $ts = $value;
            }
            if ($key === 'v1') {
                $v1 = $value;
            }
        }

        $dataId = strtolower((string) ($request->input('data.id') ?? $request->query('data.id', '')));

        if ($ts === null || $v1 === null || $dataId === '') {
            return false;
        }

        $manifest = "id:{$dataId};request-id:{$requestId};ts:{$ts};";
        $expected = hash_hmac('sha256', $manifest, (string) $secret);

        return hash_equals($expected, (string) $v1);
    }

    protected function apiUrl(string $path): string
    {
        return rtrim((string) config('chatbox.mercadopago.api_base', 'https://api.mercadopago.com'), '/').$path;
    }
}
