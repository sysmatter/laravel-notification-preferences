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
                __DIR__.'/../config/notification-preferences.php' => config_path('notification-preferences.php'),
            ], 'notification-preferences-config');
        }

        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'package-name');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'package-name');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        // if ($this->app->runningInConsole()) {
        //    $this->publishes([
        //        __DIR__.'/../config/config.php' => config_path('package-name.php'),
        //    ], 'config');
        //
        //    // Publishing the views.
        //    /*$this->publishes([
        //        __DIR__.'/../resources/views' => resource_path('views/vendor/package-name'),
        //    ], 'views');*/
        //
        //    // Publishing assets.
        //    /*$this->publishes([
        //        __DIR__.'/../resources/assets' => public_path('vendor/package-name'),
        //    ], 'assets');*/
        //
        //    // Publishing the translation files.
        //    /*$this->publishes([
        //        __DIR__.'/../resources/lang' => resource_path('lang/vendor/package-name'),
        //    ], 'lang');*/
        //
        //    // Registering package commands.
        //    // $this->commands([]);
        // }
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
