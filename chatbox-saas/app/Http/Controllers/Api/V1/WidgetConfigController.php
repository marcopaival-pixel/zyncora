<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Support\LgpdConsentToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class WidgetConfigController extends Controller
{
    public function show(string $slug): JsonResponse
    {
        $cacheKey = "widget_config_{$slug}";

        $config = Cache::remember($cacheKey, now()->addDay(), function () use ($slug) {
            $company = Company::query()
                ->where('slug', $slug)
                ->where('status', 'active')
                ->first();

            if (! $company) {
                return null;
            }

            return [
                'slug' => $company->slug,
                'name' => $company->name,
                'chat_color' => $company->chat_color,
                'logo_url' => $company->logo_path ? asset('storage/'.$company->logo_path) : null,
                'welcome_message' => $company->welcome_message,
                'lgpd' => [
                    'company_id' => $company->id,
                    'consent_token' => LgpdConsentToken::make($company),
                ],
                'broadcasting' => [
                    'key' => config('broadcasting.connections.reverb.key'),
                    'host' => config('broadcasting.connections.reverb.options.host'),
                    'port' => config('broadcasting.connections.reverb.options.port'),
                    'scheme' => config('broadcasting.connections.reverb.options.scheme'),
                ],
            ];
        });

        if (! $config) {
            abort(404, 'Widget not found or inactive.');
        }

        return response()->json($config);
    }
}
