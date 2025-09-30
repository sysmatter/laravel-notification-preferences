<?php

declare(strict_types=1);

namespace SysMatter\PackageName;

use Illuminate\Support\ServiceProvider;

class PackageNameServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
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
        // Automatically apply the package configuration
        // $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'package-name');
    }
}
