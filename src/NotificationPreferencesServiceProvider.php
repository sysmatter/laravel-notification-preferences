<?php

namespace SysMatter\NotificationPreferences;

use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use SysMatter\NotificationPreferences\Console\UninstallCommand;

class NotificationPreferencesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/notification-preferences.php',
            'notification-preferences'
        );

        $this->app->singleton(NotificationPreferenceManager::class);
        $this->app->singleton(NotificationChannelFilter::class);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/notification-preferences.php' => config_path('notification-preferences.php'),
        ], 'notification-preferences-config');

        $this->publishes([
            __DIR__ . '/../database/migrations/create_notification_preferences_table.php' => database_path('migrations/' . date('Y_m_d_His') . '_create_notification_preferences_table.php'),
        ], 'notification-preferences-migrations');

        // Only load migrations if they haven't been published
        if (!$this->migrationsHaveBeenPublished()) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }

        // Register automatic channel filtering
        Event::listen(
            NotificationSending::class,
            [NotificationChannelFilter::class, 'handle']
        );

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                UninstallCommand::class,
            ]);
        }
    }

    protected function migrationsHaveBeenPublished(): bool
    {
        $publishedPath = database_path('migrations');
        $migrationFile = 'create_notification_preferences_table.php';

        if (!is_dir($publishedPath)) {
            return false;
        }

        $files = scandir($publishedPath);
        foreach ($files as $file) {
            if (str_contains($file, $migrationFile)) {
                return true;
            }
        }

        return false;
    }
}
