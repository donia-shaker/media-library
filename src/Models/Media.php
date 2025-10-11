<?php

namespace DoniaShaker\MediaLibrary\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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

        return $directory . $type . '/' . $folder . '/' . $this->model . '/' . $this->model_id . '-' . $this->file_name . '.' . $this->format;
    }

    public function getThumbUrlAttribute()
    {
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
}
