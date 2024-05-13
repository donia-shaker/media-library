<?php

return [
    'publicPath' => public_path('media'),

    'storagePath' => storage_path('app/public/media'),

    // use `$storagePath` if true, otherwise use `$publicPath`
    'useStorage' => env('MEDIA_uSE_STORAGE', false),

    // make sure that the APP_URL is set in .env
    'publicUrl' => env('APP_URL').'/media',
    'storageUrl' => env('APP_URL').'/storage/media',

];
