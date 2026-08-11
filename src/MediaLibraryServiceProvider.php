<?php

namespace DoniaShaker\MediaLibrary;

use Illuminate\Support\ServiceProvider;

class MediaLibraryServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/media.php' => config_path('media.php'),
        ], 'media-library-config');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'media-library-migrations');

        /*
         * Run package migrations automatically
         */
        $this->loadMigrationsFrom(
            __DIR__.'/../database/migrations'
        );

        /*
         * Load private media routes automatically
         */
        $this->loadRoutesFrom(
            __DIR__.'/../routes/media.php'
        );
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/media.php',
            'media'
        );
    }
}