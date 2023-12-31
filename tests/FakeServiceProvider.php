<?php

namespace Dipantry\Analytics\Tests;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class FakeServiceProvider extends BaseServiceProvider
{
    public function register()
    {
        parent::register();
    }

    public function boot()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/analytics.php',
            'analytics',
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

    protected function isLumen(): bool
    {
        return !$this->isLaravel();
    }

    protected function isLaravel(): bool
    {
        return app() instanceof Application;
    }
}