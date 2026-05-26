import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/landing.css',
                'resources/js/app.js',
                'resources/js/widget.js',

                'resources/js/filament-echo.js',
            ],
            refresh: true,
        }),
    ],
});
