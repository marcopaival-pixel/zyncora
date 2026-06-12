<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\WidgetAccessLogRepositoryInterface;
use App\Repositories\EloquentWidgetAccessLogRepository;
use App\Repositories\ClickHouseWidgetAccessLogRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(WidgetAccessLogRepositoryInterface::class, function ($app) {
            // Se LOG_DRIVER for clickhouse, injeta o repositório colunar
            if (config('logging.widget_driver') === 'clickhouse') {
                return new ClickHouseWidgetAccessLogRepository();
            }

            // Fallback padrão: MariaDB/MySQL
            return new EloquentWidgetAccessLogRepository();
        });

        $this->app->bind(\App\Services\Fraud\Contracts\FraudDetectorInterface::class, function ($app) {
            if (config('services.fraud.driver') === 'aws') {
                return new \App\Services\Fraud\AwsFraudDetector();
            }

            return $app->make(\App\Services\Fraud\HeuristicFraudDetector::class);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
