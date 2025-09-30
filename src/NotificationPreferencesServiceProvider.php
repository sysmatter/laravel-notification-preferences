<?php

declare(strict_types=1);

namespace SysMatter\NotificationPreferences;

use Illuminate\Support\ServiceProvider;

class NotificationPreferencesServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        if (app()->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../database/migrations/' => database_path('migrations'),
            ], 'notification-preferences-migrations');

            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('notification-preferences.php'),
            ], 'notification-preferences-config');
        }
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'notification-preferences');

        $this->app->singleton(NotificationPreferenceManager::class);
        $this->app->singleton(NotificationRegistry::class);
    }
}
