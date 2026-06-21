<?php

namespace DoniaShaker\MediaLibrary;

use DoniaShaker\MediaLibrary\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class MediaController
{
    public $manager;

    protected $directory;

    protected $media;

    protected $config;

    /**
     * Constructor for the class.
     *
     * @param  mixed  $media  The media object (default: null)
     * @return void
     */
    public function __construct($media = null)
    {
        $this->media = $media;
        $this->manager = new ImageManager(new Driver());
        config('media');
        $this->directory = config('media.useStorage') ? config('media.storagePath') : config('media.publicPath');
    }

    /**
     * Uploads an image for a specific model.
     *
     * @param  mixed  $model  The model for which the image is being uploaded.
     * @param  mixed  $model_id  The ID of the model.
     * @param  mixed  $file  The file to be uploaded.
     * @return array Data about the uploaded image.
     *
     * @throws \Exception If an error occurs during the image upload process.
     */
    public function uploadImage($model, $model_id, $file, $format = null)
    {

        if (!is_dir($this->directory . '/images/' . $model)) {
            mkdir(($this->directory . '/images/' . $model), 0777, true);
        }

        $data['name'] = date('YmdHis') . '-' . uniqid();

        $data['extension'] = $format == null ? config('media.default_image_format') : $format;


        $data['file_name'] = $model . '/' . $model_id . '-' . $data['name'] . '.' . $data['extension'];

        try {
            $this->manager
                ->read($file)
                ->encodeByExtension($data['extension'], 80)
                ->save($this->directory . '/images/' . $data['file_name']);
            $data['image'] = $data['file_name'];
        } catch (\Exception $e) {
            $data['image'] = null;
        }

        return $data;
    }

    /**
     * A description of the entire PHP function.
     *
     * @param  mixed  $model  The model for which the image is being uploaded.
     * @param  mixed  $model_id  The ID of the model.
     * @param  mixed  $name  The name of file to be uploaded.
     * @return array Data about the created thumbnail.
     *
     * @throws \Exception Description of exception if it occurs.
     */
    public function createThumb($model, $model_id, $name, $format = null)
    {

        if (!file_exists($this->directory . '/images/' . $model . '/thumb/')) {
            mkdir(($this->directory . '/images/' . $model . '/thumb/'), 0777, true);
        }
        $thumb_name = $model . '/thumb/' . $model_id . '-' . $name . '.webp';
        $data['extension'] = $format == null ? config('media.default_image_format') : $format;

        try {
            $this->manager
                ->read($this->directory . '/images/' . $model . '/' . $model_id . '-' . $name . '.' . $data['extension'])->scale(width: 400)->save($this->directory . '/images/' . $thumb_name);
            $data['thumb'] = $thumb_name;
        } catch (\Exception $e) {
            $data['thumb'] = null;
        }

        return $data;
    }

    /**
     * Saves an image for a specific model.
     *
     * @param  mixed  $model  The model for which the image is being saved.
     * @param  mixed  $model_id  The ID of the model.
     * @param  mixed  $file  The file to be saved.
     * @return array Data about the saved image and thumbnail.
     */
    public function saveImage($model, $model_id, $file, $format = null)
    {
        $data['image'] = $this->uploadImage($model, $model_id, $file, $format);
        if ($data['image']['image'] == null) {
            $data['image'] = $this->uploadImage($model, $model_id, $file, $format);
        }

        $new_image = Media::create([
            'model' => $model,
            'model_id' => $model_id,
            'file_name' => $data['image']['name'],
            'format' => $data['image']['extension'],
        ]);

        // upload thumb
        $data['thumb'] = $this->createThumb($model, $model_id, $data['image']['name']);
        if ($data['thumb']['thumb'] == null) {
            $data['thumb'] = $this->createThumb($model, $model_id, $data['image']['name']);
        } else {
            $new_image->has_thumb = 1;
            $new_image->save();
        }

        return $data;
    }

    /**
     * Uploads a temporary image for a specific model.
     *
     * @param  mixed  $model  The model for which the image is being uploaded.
     * @param  mixed  $model_id  The ID of the model.
     * @param  mixed  $file  The file to be uploaded.
     * @return array Data about the uploaded image.
     */
    public function uploadTempImage($model, $model_id, $file)
    {
        if (!file_exists($this->directory . '/temp/images/' . $model)) {
            mkdir(($this->directory . '/temp/images/' . $model), 0777, true);
        }

        $data['name'] = date('YmdHis') . '-' . uniqid();
        $data['extension'] = explode('.', $file->getClientOriginalName())[1];

        $data['file_name'] = $model . '/' . $model_id . '-' . $data['name'] . '.' . $data['extension'];

        $this->manager
            ->read($file)
            ->save($this->directory . '/temp/images/' . $data['file_name']);

        $data['image'] = $data['file_name'];

        return $data;
    }

    /**
     * Saves a temporary image for a specific model.
     *
     * @param  mixed  $model  The model for which the image is being saved.
     * @param  mixed  $model_id  The ID of the model.
     * @param  mixed  $file  The file to be saved.
     * @return array Data about the saved image.
     */
    public function saveTempImage(string $model, int $model_id, UploadedFile $file)
    {
        $data['image'] = $this->uploadTempImage($model, $model_id, $file);
        if ($data['image']['image'] == null) {
            $data['image'] = $this->uploadTempImage($model, $model_id, $file);
        }

        $new_image = Media::create([
            'model' => $model,
            'model_id' => $model_id,
            'file_name' => $data['image']['name'],
            'format' => $data['image']['extension'],
            'is_active' => 0,
            'is_temp' => 1,
        ]);

        return $data;
    }

    /**
     * Converts a temporary image to a permanent image and deletes the temporary image.
     *
     * @param  string  $model  The model for which the image is being converted.
     * @param  int  $model_id  The ID of the model.
     * @param  int  $id  The ID of the temporary image in media.
     * @return JsonResponse The JSON response indicating the success of the conversion.
     */
    public function convertTempImage(string $model, int $model_id, int $id): JsonResponse
    {
        $image = Media::where('id', $id)->first();

        $main_image = $this->manager->read($this->directory . '/temp/images/' . $image->model . '/' . $image->model_id . '-' . $image->file_name . '.' . $image->format);

        $this->saveImage($model, $model_id, $main_image);

        $this->deleteTemp($id);

        return response()->json([
            'message' => 'success',
        ], 200);
    }

    /**
     * Deletes a temporary image.
     *
     * @param  int  $id  The ID of the image to delete.
     * @return JsonResponse The JSON response indicating the success of the deletion.
     */
    public function deleteTemp($id): JsonResponse
    {
        $image = Media::where('id', $id)->first();

        if (!file_exists($this->directory . '/temp/images/' . $image->model . '/' . $image->model_id . '-' . $image->file_name . '.' . $image->format) || $image->is_temp == 0) {
            return response()->json([
                'message' => 'There is no image file to delete or its not a temp image',
            ], 500);
        } else {
            File::delete($this->directory . '/temp/images/' . $image->model . '/' . $image->model_id . '-' . $image->file_name . '.' . $image->format);
        }

        $image->delete();

        return response()->json([
            'message' => 'success',
        ], 200);
    }

    /**
     * Uploads a file for a specific model.
     *
     * @param  string  $model  The model for which the file is being uploaded.
     * @param  int  $model_id  The ID of the model.
     * @param  UploadedFile  $file  The file to be uploaded.
     * @return JsonResponse The JSON response indicating the success of the upload.
     */
    public function uploadFile(
        string $model,
        int $model_id,
        UploadedFile $file
    ): JsonResponse {
        if (!$file->isValid()) {
            return response()->json([
                'message' => 'الملف المرفوع غير صالح.',
                'error' => $file->getErrorMessage(),
            ], 422);
        }

        // يجب قراءة معلومات الملف قبل move
        $originalName = pathinfo(
            $file->getClientOriginalName(),
            PATHINFO_FILENAME
        );

        $extension = strtolower(
            $file->getClientOriginalExtension()
        );

        $safeOriginalName = preg_replace(
            '/[^A-Za-z0-9_-]/',
            '-',
            $originalName
        );

        $data['name'] = now()->format('YmdHis')
            . '-'
            . uniqid()
            . '-'
            . trim($safeOriginalName, '-');

        // اسم الملف فقط، بدون model/
        $storedFileName = $model_id
            . '-'
            . $data['name']
            . '.'
            . $extension;

        $destinationDirectory = $this->directory
            . DIRECTORY_SEPARATOR
            . $extension
            . DIRECTORY_SEPARATOR
            . $model;

        File::ensureDirectoryExists(
            $destinationDirectory,
            0755,
            true
        );

        try {
            // يجب أن يكون آخر استخدام لكائن الملف
            $file->move(
                $destinationDirectory,
                $storedFileName
            );

            $media = Media::create([
                'model' => $model,
                'model_id' => $model_id,
                'file_name' => $data['name'],
                'format' => $extension,
            ]);

            return response()->json([
                'message' => 'success',
                'data' => [
                    'id' => $media->id,
                    'file_name' => $storedFileName,
                    'path' => $extension
                        . '/'
                        . $model
                        . '/'
                        . $storedFileName,
                ],
            ], 200);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'حدث خطأ أثناء رفع الملف.',
                'error' => $exception->getMessage(),
            ], 500);
        }
    }

    /**
     * Uploads an audio file for a specific model.
     *
     * @param  string  $model  The model for which the audio file is being uploaded.
     * @param  int  $model_id  The ID of the model.
     * @param  UploadedFile  $file  The audio file to be uploaded.
     * @return JsonResponse The JSON response indicating the success of the upload.
     */
    public function audio(
        string $model,
        int $model_id,
        UploadedFile $file
    ): JsonResponse {
        if (!$file->isValid()) {
            return response()->json([
                'message' => 'ملف الصوت غير صالح.',
                'error' => $file->getErrorMessage(),
            ], 422);
        }

        $originalName = pathinfo(
            $file->getClientOriginalName(),
            PATHINFO_FILENAME
        );

        $extension = strtolower(
            $file->getClientOriginalExtension()
        );

        $safeOriginalName = preg_replace(
            '/[^A-Za-z0-9_-]/',
            '-',
            $originalName
        );

        $name = now()->format('YmdHis')
            . '-'
            . uniqid()
            . '-'
            . trim($safeOriginalName, '-');

        $storedFileName = $model_id
            . '-'
            . $name
            . '.'
            . $extension;

        $destinationDirectory = $this->directory
            . DIRECTORY_SEPARATOR
            . 'audio'
            . DIRECTORY_SEPARATOR
            . $model;

        File::ensureDirectoryExists(
            $destinationDirectory,
            0755,
            true
        );

        try {
            $file->move(
                $destinationDirectory,
                $storedFileName
            );

            $media = Media::create([
                'model' => $model,
                'model_id' => $model_id,
                'file_name' => $name,
                'format' => $extension,
            ]);

            return response()->json([
                'message' => 'success',
                'data' => [
                    'id' => $media->id,
                    'file_name' => $storedFileName,
                    'path' => 'audio/'
                        . $model
                        . '/'
                        . $storedFileName,
                ],
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'حدث خطأ أثناء رفع الصوت.',
                'error' => $exception->getMessage(),
            ], 500);
        }
    }

    /**
     * Uploads a video file for a specific model.
     *
     * @param  mixed  $model  The model for which the video is being uploaded.
     * @param  mixed  $model_id  The ID of the model.
     * @param  mixed  $file  The video file to be uploaded.
     * @return JsonResponse The JSON response indicating the success of the video upload.
     *
     * @throws \Exception If an error occurs during the video upload process.
     */
    public function video(
        string $model,
        int $model_id,
        UploadedFile $file
    ): JsonResponse {
        if (!$file->isValid()) {
            return response()->json([
                'message' => 'ملف الفيديو غير صالح.',
                'error' => $file->getErrorMessage(),
            ], 422);
        }

        $originalName = pathinfo(
            $file->getClientOriginalName(),
            PATHINFO_FILENAME
        );

        $extension = strtolower(
            $file->getClientOriginalExtension()
        );

        $safeOriginalName = preg_replace(
            '/[^A-Za-z0-9_-]/',
            '-',
            $originalName
        );

        $name = now()->format('YmdHis')
            . '-'
            . uniqid()
            . '-'
            . trim($safeOriginalName, '-');

        $storedFileName = $model_id
            . '-'
            . $name
            . '.'
            . $extension;

        $destinationDirectory = $this->directory
            . DIRECTORY_SEPARATOR
            . 'video'
            . DIRECTORY_SEPARATOR
            . $model;

        File::ensureDirectoryExists(
            $destinationDirectory,
            0755,
            true
        );

        try {
            $file->move(
                $destinationDirectory,
                $storedFileName
            );

            $media = Media::create([
                'model' => $model,
                'model_id' => $model_id,
                'file_name' => $name,
                'format' => $extension,
            ]);

            return response()->json([
                'message' => 'success',
                'data' => [
                    'id' => $media->id,
                    'file_name' => $storedFileName,
                    'path' => 'video/'
                        . $model
                        . '/'
                        . $storedFileName,
                ],
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'حدث خطأ أثناء رفع الفيديو.',
                'error' => $exception->getMessage(),
            ], 500);
        }
    }
}
