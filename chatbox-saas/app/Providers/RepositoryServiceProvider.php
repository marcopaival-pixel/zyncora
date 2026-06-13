<?php

namespace App\Providers;

use App\Repositories\ClickHouseWidgetAccessLogRepository;
use App\Repositories\Contracts\WidgetAccessLogRepositoryInterface;
use App\Repositories\EloquentWidgetAccessLogRepository;
use App\Services\Fraud\AwsFraudDetector;
use App\Services\Fraud\Contracts\FraudDetectorInterface;
use App\Services\Fraud\HeuristicFraudDetector;
use Illuminate\Support\ServiceProvider;

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
                return new ClickHouseWidgetAccessLogRepository;
            }

            // Fallback padrão: MariaDB/MySQL
            return new EloquentWidgetAccessLogRepository;
        });

        $this->app->bind(FraudDetectorInterface::class, function ($app) {
            if (config('services.fraud.driver') === 'aws') {
                return new AwsFraudDetector;
            }

            return $app->make(HeuristicFraudDetector::class);
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
