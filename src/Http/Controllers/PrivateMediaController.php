<?php

namespace DoniaShaker\MediaLibrary\Http\Controllers;

use DoniaShaker\MediaLibrary\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class PrivateMediaController extends Controller
{
    public function show(
        Request $request,
        Media $media
    ) {
        /*
         * 1. Signed URL must be valid
         */
        if (!$request->hasValidSignature()) {
            abort(404);
        }

        /*
         * 2. Media must actually be private
         */
        if ($media->visibility !== 'private') {
            abort(404);
        }

        /*
         * 3. Get auth guard from package config
         *
         * Default: sanctum
         * JWT project can use:
         * MEDIA_PRIVATE_AUTH_GUARD=api
         */
        $guard = config(
            'media.privateAuthGuard',
            'sanctum'
        );

        /*
         * 4. Get current authenticated user
         */
        try {
            $user = Auth::guard($guard)->user();
        } catch (\Throwable $e) {
            abort(404);
        }

        if (!$user) {
            abort(404);
        }

        /*
         * 5. Signed URL must belong to
         * current authenticated user
         */
        if (
            (string) $request->query('auth_id')
            !==
            (string) $user->getAuthIdentifier()
        ) {
            abort(404);
        }

        /*
         * 6. Original file or thumbnail
         */
        if ($request->boolean('thumb')) {

            /*
             * Thumbnail only makes sense for images
             */
            if (!$media->isImageFormat($media->format)) {
                abort(404);
            }

            $relativePath =
                'images/'
                . $media->model
                . '/thumb/'
                . $media->model_id
                . '-'
                . $media->file_name
                . '.'
                . $media->format;

        } else {

            $relativePath =
                $media->getRelativePath();
        }

        /*
         * 7. Build physical private file path
         */
        $basePath = rtrim(
            config('media.privatePath'),
            DIRECTORY_SEPARATOR
        );

        $relativePath = str_replace(
            '/',
            DIRECTORY_SEPARATOR,
            $relativePath
        );

        $fullPath =
            $basePath
            . DIRECTORY_SEPARATOR
            . $relativePath;

        /*
         * 8. File must exist
         */
        if (!is_file($fullPath)) {
            abort(404);
        }

        /*
         * 9. Return file securely
         */
        return response()->file(
            $fullPath,
            [
                'Cache-Control' =>
                    'private, no-store, max-age=0',

                'Pragma' =>
                    'no-cache',

                'X-Content-Type-Options' =>
                    'nosniff',
            ]
        );
    }
}