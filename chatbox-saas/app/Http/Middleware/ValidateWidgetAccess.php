<?php

namespace App\Http\Middleware;

use App\Jobs\ProcessWidgetAccessLog;
use App\Models\ChatbotSecurityToken;
use App\Services\WidgetFingerprintService;
use App\Services\WidgetSecurityService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ValidateWidgetAccess
{
    protected WidgetSecurityService $securityService;

    protected WidgetFingerprintService $fingerprintService;

    public function __construct(WidgetSecurityService $securityService, WidgetFingerprintService $fingerprintService)
    {
        $this->securityService = $securityService;
        $this->fingerprintService = $fingerprintService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $publicToken = $request->route('token') ?? $request->header('X-Widget-Token');

        if (! $publicToken) {
            return response()->json(['error' => 'Missing widget token'], 403)
                ->header('Cache-Control', 'max-age=60'); // Edge caching for failed auth
        }

        // Cache resolution for token -> chatbot
        $tokenRecord = Cache::remember("widget_token:{$publicToken}", 3600, function () use ($publicToken) {
            return ChatbotSecurityToken::with('chatbot.company.plan')->where('public_token', $publicToken)->first();
        });

        if (! $tokenRecord || ! $tokenRecord->chatbot) {
            return response()->json(['error' => 'Invalid token'], 403)
                ->header('Cache-Control', 'max-age=60');
        }

        $chatbot = $tokenRecord->chatbot;
        $domain = $request->header('Origin') ?? $request->header('Referer') ?? 'unknown';

        // Handle Rate Limiting
        $rateLimit = $chatbot->company->plan->rate_limit_per_minute ?? 60;
        $rateLimitKey = "widget_rate_limit:{$chatbot->id}:".$request->ip();

        if (RateLimiter::tooManyAttempts($rateLimitKey, $rateLimit)) {
            $this->logAccess($request, $chatbot, $domain, 'blocked', 'Rate limit exceeded');

            return response()->json(['error' => 'Too Many Requests'], 429)
                ->header('Retry-After', RateLimiter::availableIn($rateLimitKey));
        }
        RateLimiter::hit($rateLimitKey, 60);

        // Validation 1: License
        if (! $this->securityService->validateLicense($chatbot)) {
            $this->logAccess($request, $chatbot, $domain, 'blocked', 'Invalid or expired license');

            return response()->json(['error' => 'License inactive'], 403);
        }

        // Validation 2: Domain Access
        if (! $this->securityService->validateDomainAccess($chatbot, $domain)) {
            $this->logAccess($request, $chatbot, $domain, 'blocked', 'Domain not authorized');

            return response()->json(['error' => 'Domain not authorized'], 403)
                ->header('Cache-Control', 'max-age=60');
        }

        // Success - log allowed access
        $this->logAccess($request, $chatbot, $domain, 'allowed', null);

        // Add chatbot to request attributes to be used in controller
        $request->attributes->set('chatbot', $chatbot);

        return $next($request);
    }

    protected function logAccess(Request $request, $chatbot, $domain, $status, $reason)
    {
        $fingerprint = $this->fingerprintService->generateFingerprint($request, $chatbot);
        $sessionId = $request->header('X-Widget-Session-Id') ?? Str::uuid()->toString();

        $logData = [
            'chatbot_id' => $chatbot->id,
            'domain' => $domain,
            'ip_address' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
            'session_id' => $sessionId,
            'fingerprint_hash' => $fingerprint,
            'status' => $status,
            'block_reason' => $reason,
        ];

        // Dispatch background job to prevent blocking the response
        ProcessWidgetAccessLog::dispatch($logData);
    }
}
