<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HealthAlertService
{
    /**
     * @param  array<int, array{label: string, status: string, message: string}>  $checks
     */
    public function dispatch(string $summary, string $status, array $checks): void
    {
        Log::warning($summary, [
            'health_status' => $status,
            'checks' => $checks,
        ]);

        $this->sendToSentry($summary);
        $this->sendToSlack($summary, $status, $checks);
        $this->sendToWebhook($summary, $status, $checks);
    }

    protected function sendToSentry(string $summary): void
    {
        if (! function_exists('Sentry\\captureMessage')) {
            return;
        }

        if (! filled(config('sentry.dsn') ?: env('SENTRY_LARAVEL_DSN'))) {
            return;
        }

        \Sentry\captureMessage($summary);
    }

    /**
     * @param  array<int, array{label: string, status: string, message: string}>  $checks
     */
    protected function sendToSlack(string $summary, string $status, array $checks): void
    {
        $webhookUrl = config('chatbox.monitoring.health_alert_slack_webhook_url');

        if (! filled($webhookUrl)) {
            return;
        }

        $color = match ($status) {
            'critical' => '#dc2626',
            'degraded' => '#ca8a04',
            default => '#16a34a',
        };

        $fields = collect($checks)
            ->filter(fn (array $check) => in_array($check['status'], ['fail', 'warn'], true))
            ->map(fn (array $check) => [
                'title' => $check['label'],
                'value' => $check['message'],
                'short' => false,
            ])
            ->values()
            ->all();

        try {
            Http::timeout(10)->post($webhookUrl, [
                'text' => $summary,
                'attachments' => [[
                    'color' => $color,
                    'title' => 'Chatbox SaaS — health check',
                    'text' => $summary,
                    'fields' => $fields,
                    'footer' => config('app.name'),
                    'ts' => now()->timestamp,
                ]],
            ]);
        } catch (\Throwable $e) {
            Log::error('health_alert_slack_failed', ['message' => $e->getMessage()]);
        }
    }

    /**
     * @param  array<int, array{label: string, status: string, message: string}>  $checks
     */
    protected function sendToWebhook(string $summary, string $status, array $checks): void
    {
        $webhookUrl = config('chatbox.monitoring.health_alert_webhook_url');

        if (! filled($webhookUrl)) {
            return;
        }

        try {
            Http::timeout(10)->post($webhookUrl, [
                'source' => 'chatbox-health',
                'application' => config('app.name'),
                'environment' => app()->environment(),
                'status' => $status,
                'summary' => $summary,
                'checked_at' => now()->toIso8601String(),
                'checks' => $checks,
            ]);
        } catch (\Throwable $e) {
            Log::error('health_alert_webhook_failed', ['message' => $e->getMessage()]);
        }
    }
}
