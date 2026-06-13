<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Plan;
use Illuminate\Support\Facades\Cache;

class PlanApiController extends Controller
{
    public function index()
    {
        $plans = Cache::rememberForever('public_plans', function () {
            return Plan::where('is_active', true)
                ->orderBy('sort_order', 'asc')
                ->get();
        });

        return response()->json([
            'success' => true,
            'data' => $plans
        ]);
    }
}
