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
         * 3. Get authenticated user from
         * multiple configured guards
         */
        $user = $this->getCurrentPrivateAuth();

        if (!$user) {
            abort(404);
        }

        /*
         * 4. Signed URL must belong to
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
         * 5. Original file or thumbnail
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

            $relativePath = $media->getRelativePath();
        }

        /*
         * 6. Build physical private file path
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
         * 7. File must exist
         */
        if (!is_file($fullPath)) {
            abort(404);
        }

        /*
         * 8. Return file securely
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

    /**
     * Get authenticated user from any
     * configured private media guard.
     */
    protected function getCurrentPrivateAuth()
    {
        $guards = config(
            'media.privateAuthGuards',
            'sanctum'
        );

        /*
         * Support:
         *
         * privateAuthGuards => 'sanctum,api,admin'
         *
         * or:
         *
         * privateAuthGuards => [
         *     'sanctum',
         *     'api',
         *     'admin',
         * ]
         */

        if (is_string($guards)) {
            $guards = explode(',', $guards);
        }

        $guards = array_filter(
            array_map(
                'trim',
                $guards
            )
        );

        foreach ($guards as $guard) {
            try {
                $user = Auth::guard($guard)->user();

                if ($user) {
                    return $user;
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        return null;
    }
}
