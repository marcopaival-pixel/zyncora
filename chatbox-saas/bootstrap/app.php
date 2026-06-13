<?php

use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\EnsureApiDocsBasicAuth;
use App\Http\Middleware\EnsureApiDocsEnabled;
use App\Http\Middleware\EnsureDemoRoutesEnabled;
use App\Http\Middleware\EnsureHealthCheckToken;
use App\Http\Middleware\EnsureSingleSession;
use App\Http\Middleware\LogPerformanceMiddleware;
use App\Http\Middleware\SecurityHeadersMiddleware;
use App\Http\Middleware\SessionTimeoutMiddleware;
use App\Http\Middleware\TenantMiddleware;
use App\Http\Middleware\ValidateWidgetAccess;
use App\Http\Middleware\WhiteLabelMiddleware;
use App\Models\SystemErrorLog;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withBroadcasting(
        __DIR__.'/../routes/channels.php',
        [
            'middleware' => ['web', 'auth'],
        ],
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(LogPerformanceMiddleware::class);
        $middleware->appendToGroup('web', [
            SecurityHeadersMiddleware::class,
            SessionTimeoutMiddleware::class,
            EnsureSingleSession::class,
            WhiteLabelMiddleware::class,
        ]);
        $middleware->appendToGroup('api', TenantMiddleware::class);
        $middleware->alias([
            'permission' => CheckPermission::class,
            'demo.enabled' => EnsureDemoRoutesEnabled::class,
            'api.docs' => EnsureApiDocsEnabled::class,
            'api.docs.auth' => EnsureApiDocsBasicAuth::class,
            'health.token' => EnsureHealthCheckToken::class,
            'white_label' => WhiteLabelMiddleware::class,
            'widget.access' => ValidateWidgetAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->report(function (Throwable $e) {
            try {
                $maskPayload = function (array $payload) use (&$maskPayload): array {
                    $sensitiveKeys = [
                        'password',
                        'password_confirmation',
                        'current_password',
                        'token',
                        'access_token',
                        'refresh_token',
                        'authorization',
                        'api_key',
                        'secret',
                        'email',
                        'phone',
                        'client_phone',
                        'client_email',
                        'cpf',
                        'cnpj',
                    ];

                    foreach ($payload as $key => $value) {
                        $normalizedKey = strtolower((string) $key);

                        if (in_array($normalizedKey, $sensitiveKeys, true)) {
                            $payload[$key] = '[masked]';

                            continue;
                        }

                        if (is_array($value)) {
                            $payload[$key] = $maskPayload($value);

                            continue;
                        }

                        if (is_string($value) && mb_strlen($value) > 500) {
                            $payload[$key] = mb_substr($value, 0, 500).'... [truncated]';
                        }
                    }

                    return $payload;
                };

                $payload = request()->all() ? $maskPayload(request()->all()) : null;
                $user = Auth::user();

                SystemErrorLog::create([
                    'user_id' => $user?->id,
                    'company_id' => $user?->company_id,
                    'type' => match (true) {
                        $e instanceof QueryException => 'SQL',
                        $e instanceof ValidationException => 'Validation',
                        request()->is('api/*') => 'API',
                        default => 'Exception',
                    },
                    'message' => mb_substr($e->getMessage(), 0, 2000),
                    'url' => request()->fullUrl(),
                    'method' => request()->method(),
                    'ip_address' => request()->ip(),
                    'payload' => $payload,
                    'stack_trace' => mb_substr($e->getTraceAsString(), 0, 12000),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'status_code' => $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500,
                ]);
            } catch (Throwable $th) {
                // Falha silenciosa para evitar loops
            }
        });
    })->create();
