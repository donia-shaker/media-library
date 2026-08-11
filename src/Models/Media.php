<?php

namespace DoniaShaker\MediaLibrary\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

class Media extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $appends = ['url', 'thumb_url'];

    public function getUrlAttribute()
    {
        $directory = config('media.useStorage') ? config('media.storageUrl') : config('media.publicUrl');
        $type = $this->is_temp ? '/temp' : '';

        if ($this->isImageFormat($this->format)) {
            $folder = 'images';
        } elseif ($this->isVideoFormat($this->format)) {
            $folder = 'video';
        } elseif ($this->isAudioFormat($this->format)) {
            $folder = 'audio';
        } else {
            $folder = $this->format; // أي نوع آخر
        }

        $path =
            $type
            . '/'
            . $folder
            . '/'
            . $this->model
            . '/'
            . $this->model_id
            . '-'
            . $this->file_name
            . '.'
            . $this->format;

        if ($this->visibility === 'private') {

            $user = $this->getCurrentPrivateAuth();

            if (!$user) {
                return null;
            }

            return URL::signedRoute(
                'media-library.private.show',
                [
                    'media' => $this->id,
                    'auth_guard' => $user['guard'],
                    'auth_id' => $user['user']->getAuthIdentifier(),
                ]
            );
        }

        return $directory . $path;
    }

    public function getThumbUrlAttribute()
    {
        if ($this->is_temp || !$this->has_thumb) {
            return null;
        }
        if ($this->visibility === 'private') {

            if ($this->is_temp) {
                return null;
            }

            $user = $this->getCurrentPrivateAuth();

            if (!$user) {
                return null;
            }

            return URL::signedRoute(
                'media-library.private.show',
                [
                    'media' => $this->id,
                    'auth_id' => $user['user']->getAuthIdentifier(),
                    'auth_guard' => $user['guard'],
                    'thumb' => 1,
                ]
            );
        }
        $directory = config('media.useStorage') ? config('media.storageUrl') : config('media.publicUrl');

        $type = $this->is_temp ? null : '/thumb';

        return $type == null ? null : $directory . '/images' . '/' . $this->model . $type . '/' . $this->model_id . '-' . $this->file_name . '.' . $this->format;
    }

    function isImageFormat($format)
    {
        return in_array(strtolower($format), ['jpg', 'svg', 'jpeg', 'png', 'gif', 'webp']);
    }

    // تحقق إذا الملف فيديو
    function isVideoFormat($format)
    {
        return in_array(strtolower($format), [
            'mp4',
            'mov',
            'avi',
            'mkv',
            'webm',
            'flv',
            'wmv',
            'm4v'
        ]);
    }

    // تحقق إذا الملف صوت
    function isAudioFormat($format)
    {
        return in_array(strtolower($format), [
            'mp3',
            'wav',
            'ogg',
            'm4a',
            'flac',
            'aac'
        ]);
    }
    protected function getCurrentPrivateAuth(): ?array
    {
        $guards = config(
            'media.privateAuthGuards',
            'sanctum'
        );

        if (is_string($guards)) {
            $guards = explode(',', $guards);
        }

        foreach ($guards as $guard) {
            $guard = trim($guard);

            if (!$guard) {
                continue;
            }

            try {
                $user = Auth::guard($guard)->user();

                if ($user) {
                    return [
                        'user' => $user,
                        'guard' => $guard,
                    ];
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        return null;
    }
    public function getRelativePath(): string
    {
        $type = $this->is_temp
            ? 'temp/'
            : '';

        if ($this->isImageFormat($this->format)) {
            $folder = 'images';
        } elseif ($this->isVideoFormat($this->format)) {
            $folder = 'video';
        } elseif ($this->isAudioFormat($this->format)) {
            $folder = 'audio';
        } else {
            $folder = $this->format;
        }

        return $type
            . $folder
            . '/'
            . $this->model
            . '/'
            . $this->model_id
            . '-'
            . $this->file_name
            . '.'
            . $this->format;
    }
}
