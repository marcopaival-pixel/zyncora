<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\SystemHealthMonitoringService;
use Illuminate\Http\JsonResponse;

class HealthStatusController extends Controller
{
    public function show(SystemHealthMonitoringService $monitoring): JsonResponse
    {
        $checks = $monitoring->run();
        $status = $monitoring->overallStatus($checks);

        return response()->json([
            'status' => $status,
            'checked_at' => now()->toIso8601String(),
            'checks' => $checks,
        ], $monitoring->hasCriticalFailures($checks) ? 503 : 200);
    }
}
