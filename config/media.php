<?php

return [
    'publicPath' => public_path('media'),

    'storagePath' => storage_path('app/public/media'),

    'privatePath' => storage_path('app/private/media'),

    'privateAuthGuard' => env('MEDIA_PRIVATE_AUTH_GUARD','sanctum'),

    // use `$storagePath` if true, otherwise use `$publicPath`
    'useStorage' => env('MEDIA_USE_STORAGE', false),

    // if true, the package will create thumbnails for images, otherwise it will not
    'createThumbnails' => env('MEDIA_CREATE_THUMBNAILS', true),

    // make sure that the APP_URL is set in .env
    'publicUrl' => env('APP_URL') . '/media',
    'storageUrl' => env('APP_URL') . '/storage/media',

    // Default format for image is webp, if you want to get your image format asset `default_image_format to null`
    'default_image_format' => 'webp',

];
