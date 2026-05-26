<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Content Security Policy
    |--------------------------------------------------------------------------
    |
    | CSP_ALLOW_UNSAFE_EVAL — Livewire/Filament em local pode exigir 'unsafe-eval'.
    | Em production o default é false (política mais restritiva).
    |
    */
    'csp' => [
        'allow_unsafe_eval' => env(
            'CSP_ALLOW_UNSAFE_EVAL',
            ! in_array(env('APP_ENV', 'production'), ['production', 'testing'], true)
        ),

        'script_src' => [
            "'self'",
            "'unsafe-inline'",
            'https://cdn.jsdelivr.net',
            'https://unpkg.com',
            'https://cdn.tailwindcss.com',
        ],

        'style_src' => [
            "'self'",
            "'unsafe-inline'",
            'https://fonts.googleapis.com',
            'https://cdn.jsdelivr.net',
            'https://unpkg.com',
        ],

        'font_src' => [
            "'self'",
            'https://fonts.gstatic.com',
        ],

        'img_src' => [
            "'self'",
            'data:',
            'https:',
        ],

        'connect_src' => [
            "'self'",
            'ws:',
            'wss:',
            'https://unpkg.com',
        ],
    ],

];
