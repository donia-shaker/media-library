<?php

use DoniaShaker\MediaLibrary\Http\Controllers\PrivateMediaController;
use Illuminate\Support\Facades\Route;

Route::middleware('api')
    ->prefix('api')
    ->group(function () {

        Route::get(
            '/media/private/{media}',
            [
                PrivateMediaController::class,
                'show',
            ]
        )->name('media-library.private.show');
    });