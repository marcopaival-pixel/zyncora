<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class SystemHealthCheckCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_system_health_check_passes_in_testing(): void
    {
        $this->artisan('system:health-check')
            ->assertSuccessful();
    }

    public function test_system_health_check_json_output(): void
    {
        $this->artisan('system:health-check --json')
            ->assertSuccessful()
            ->expectsOutputToContain('"status"');
    }

    public function test_system_health_check_warns_on_failed_jobs(): void
    {
        DB::table('failed_jobs')->insert([
            'uuid' => (string) Str::uuid(),
            'connection' => 'database',
            'queue' => 'default',
            'payload' => '{}',
            'exception' => 'Test failure',
            'failed_at' => now(),
        ]);

        $this->artisan('system:health-check')
            ->assertSuccessful()
            ->expectsOutputToContain('degraded');
    }
}
