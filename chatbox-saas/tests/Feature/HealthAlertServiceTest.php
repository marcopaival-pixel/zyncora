<?php

namespace Tests\Feature;

use App\Services\HealthAlertService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HealthAlertServiceTest extends TestCase
{
    public function test_dispatches_to_slack_and_generic_webhook(): void
    {
        Http::fake();

        config([
            'chatbox.monitoring.health_alert_slack_webhook_url' => 'https://hooks.slack.test/services/abc',
            'chatbox.monitoring.health_alert_webhook_url' => 'https://monitoring.test/webhook',
        ]);

        app(HealthAlertService::class)->dispatch(
            '[Chatbox] Health degraded: Fila de jobs: 60 pendentes',
            'degraded',
            [
                [
                    'label' => 'Fila de jobs',
                    'status' => 'warn',
                    'message' => '60 pendentes',
                ],
            ]
        );

        Http::assertSentCount(2);
    }
}
