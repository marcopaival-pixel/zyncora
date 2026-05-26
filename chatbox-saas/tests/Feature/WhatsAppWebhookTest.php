<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyIntegration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsAppWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function createWhatsAppIntegration(Company $company, string $verifyToken = 'my_secret_token', string $appSecret = 'test_app_secret'): CompanyIntegration
    {
        return CompanyIntegration::query()->create([
            'company_id' => $company->id,
            'driver' => 'whatsapp_cloud',
            'webhook_verify_token' => $verifyToken,
            'credentials' => ['app_secret' => $appSecret],
            'status' => 'active',
        ]);
    }

    public function test_webhook_verification_returns_challenge(): void
    {
        $company = Company::factory()->create([
            'slug' => 'test-slug',
        ]);

        $this->createWhatsAppIntegration($company);

        $challenge = '123456789';

        $response = $this->getJson('/api/v1/integrations/whatsapp/webhook/'.$company->slug.'?hub_mode=subscribe&hub_verify_token=my_secret_token&hub_challenge='.$challenge);

        $response->assertStatus(200);
        $response->assertSee($challenge);
    }

    public function test_webhook_ingests_message(): void
    {
        $company = Company::factory()->create([
            'slug' => 'test-slug',
        ]);

        $appSecret = 'test_app_secret';
        $this->createWhatsAppIntegration($company, 'my_secret_token', $appSecret);

        $payload = [
            'object' => 'whatsapp_business_account',
            'entry' => [
                [
                    'id' => '123',
                    'changes' => [
                        [
                            'value' => [
                                'messaging_product' => 'whatsapp',
                                'metadata' => [
                                    'display_phone_number' => '16505551111',
                                    'phone_number_id' => '123456123',
                                ],
                                'messages' => [
                                    [
                                        'from' => '16315551234',
                                        'id' => 'wamid.test',
                                        'timestamp' => '1602320432',
                                        'text' => [
                                            'body' => 'Hello this is a test',
                                        ],
                                        'type' => 'text',
                                    ],
                                ],
                            ],
                            'field' => 'messages',
                        ],
                    ],
                ],
            ],
        ];

        $body = json_encode($payload);
        $signature = 'sha256='.hash_hmac('sha256', $body, $appSecret);

        $response = $this->call(
            'POST',
            '/api/v1/integrations/whatsapp/webhook/'.$company->slug,
            [],
            [],
            [],
            [
                'HTTP_X-Hub-Signature-256' => $signature,
                'CONTENT_TYPE' => 'application/json',
            ],
            $body
        );

        $response->assertStatus(200);
    }
}
