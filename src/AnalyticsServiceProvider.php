<?php

namespace Dipantry\Analytics;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class AnalyticsServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/analytics.php',
            'analytics'
        );

        $databasePath = __DIR__ . '/../database/migrations';
        if ($this->isLumen()) {
            $this->loadMigrationsFrom($databasePath);
        } else {
            $this->publishes(
                [$databasePath => database_path('migrations')],
                'migrations'
            );
        }

        if (class_exists(Application::class)) {
            $this->publishes([
                __DIR__ . '/../config/analytics.php' => config_path('analytics.php'),
            ], 'config');
        }
    }

    /**
     * Check if the application is Laravel.
     *
     * @return bool
     */
    protected function isLaravel(): bool
    {
        return app() instanceof Application;
    }

    /**
     * Check if the application is Lumen.
     *
     * @return bool
     */
    protected function isLumen(): bool
    {
        return !$this->isLaravel();
    }
}