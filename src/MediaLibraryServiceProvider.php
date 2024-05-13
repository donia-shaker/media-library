<?php

namespace DoniaShaker\MediaLibrary;

use Illuminate\Support\ServiceProvider;

class MediaLibraryServiceProvider extends ServiceProvider
{
    /**
     * Initializes the application during the booting process.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/media.php' => config_path('media.php'),
        ], 'media-library-config');
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'media-library-migrations');

    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/media.php', 'media');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

    }
}
