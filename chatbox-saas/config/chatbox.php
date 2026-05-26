<?php

return [

    'whatsapp' => [
        'graph_version' => env('WHATSAPP_GRAPH_VERSION', 'v21.0'),
        'queue' => env('WHATSAPP_OUTBOUND_QUEUE', 'default'),
    ],

    'ai' => [
        'queue' => env('AI_QUEUE', 'default'),
        'gemini' => [
            'api_key' => env('GEMINI_API_KEY'),
            'model' => env('GEMINI_MODEL', 'gemini-1.5-flash'),
            'timeout' => (int) env('GEMINI_TIMEOUT', 15),
            'retry_times' => (int) env('GEMINI_RETRY_TIMES', 2),
            'retry_sleep' => (int) env('GEMINI_RETRY_SLEEP', 250),
        ],
    ],

    'plans' => [
        'features' => [
            'basic' => ['whatsapp', 'site'],
            'pro' => ['whatsapp', 'site', 'ai_automation', 'reminders'],
            'enterprise' => ['whatsapp', 'site', 'ai_automation', 'reminders', 'white_label', 'api_access'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Produção / go-live
    |--------------------------------------------------------------------------
    |
    | FILAMENT_REGISTRATION_ENABLED — registo público no painel /admin/register
    | DEMO_ROUTES_ENABLED — rotas /demo e /api/demo-*
    | BILLING_SIMULATION_ENABLED — troca de plano sem gateway (apenas dev/staging)
    | PAYMENT_DRIVER — none | stripe | mercadopago (simulation via BILLING_SIMULATION_ENABLED)
    | API_DOCS_ENABLED — portal Swagger UI em /api/docs
    |
    */
    'filament_registration_enabled' => env('FILAMENT_REGISTRATION_ENABLED', ! in_array(env('APP_ENV', 'production'), ['production', 'testing'], true)),

    'api_docs_enabled' => env('API_DOCS_ENABLED', ! in_array(env('APP_ENV', 'production'), ['production', 'testing'], true)),

    'api_docs_basic_auth' => [
        'user' => env('API_DOCS_BASIC_AUTH_USER'),
        'password' => env('API_DOCS_BASIC_AUTH_PASSWORD'),
    ],

    'demo_routes_enabled' => env('DEMO_ROUTES_ENABLED', ! in_array(env('APP_ENV', 'production'), ['production', 'testing'], true)),

    'billing_simulation_enabled' => env('BILLING_SIMULATION_ENABLED', ! in_array(env('APP_ENV', 'production'), ['production', 'testing'], true)),

    'payment_driver' => env('PAYMENT_DRIVER', 'none'),

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        'currency' => env('STRIPE_CURRENCY', 'brl'),
    ],

    'mercadopago' => [
        'access_token' => env('MERCADOPAGO_ACCESS_TOKEN'),
        'webhook_secret' => env('MERCADOPAGO_WEBHOOK_SECRET'),
        'currency' => env('MERCADOPAGO_CURRENCY', 'BRL'),
        'api_base' => env('MERCADOPAGO_API_BASE', 'https://api.mercadopago.com'),
    ],

    'subscription_expiry_warning_days' => (int) env('SUBSCRIPTION_EXPIRY_WARNING_DAYS', 7),

    'subscription_grace_period_days' => (int) env('SUBSCRIPTION_GRACE_PERIOD_DAYS', 7),

    /*
    |--------------------------------------------------------------------------
    | Monitorização operacional (P11)
    |--------------------------------------------------------------------------
    |
    | HEALTH_CHECK_TOKEN — protege GET /api/v1/health/status (Bearer ou ?token=)
    | Limites de alerta para system:health-check e dashboard admin
    |
    */
    'monitoring' => [
        'health_check_token' => env('HEALTH_CHECK_TOKEN'),
        'queue_pending_warning' => (int) env('HEALTH_QUEUE_PENDING_WARNING', 50),
        'queue_pending_critical' => (int) env('HEALTH_QUEUE_PENDING_CRITICAL', 200),
        'failed_jobs_warning' => (int) env('HEALTH_FAILED_JOBS_WARNING', 1),
        'error_log_warning_24h' => (int) env('HEALTH_ERROR_LOG_WARNING_24H', 10),
        'backup_max_age_hours' => (int) env('HEALTH_BACKUP_MAX_AGE_HOURS', 26),
        'disk_free_min_mb' => (int) env('HEALTH_DISK_FREE_MIN_MB', 500),
        'health_alert_slack_webhook_url' => env('HEALTH_ALERT_SLACK_WEBHOOK_URL'),
        'health_alert_webhook_url' => env('HEALTH_ALERT_WEBHOOK_URL'),
    ],

];
