<?php

namespace Dipantry\Analytics;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class AnalyticsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot(): void
    {
        if (class_exists(Application::class)) {
            $this->publishes([
                __DIR__.'/../config/analytics.php' => config_path('analytics.php'),
            ], 'config');
        }

        $databasePath = __DIR__.'/../database/migrations';
        if ($this->isLumen()) {
            $this->loadMigrationsFrom($databasePath);
        } else {
            $this->publishes(
                [$databasePath => database_path('migrations')],
                'migrations'
            );
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/analytics.php',
            'analytics'
        );
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