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

        if(isImageFormat($this->format))
            return $directory.$type.'/images'.'/'.$this->model.'/'.$this->model_id.'-'.$this->file_name.'.'.$this->format;
        return $directory.$type.'/'.$this->extension.'/'.$this->model.'/'.$this->model_id.'-'.$this->file_name.'.'.$this->format;
    }

    public function getThumbUrlAttribute()
    {
        $directory = config('media.useStorage') ? config('media.storageUrl') : config('media.publicUrl');

        $type = $this->is_temp ? null : '/thumb';

        return $type == null ? null : $directory.'/images'.'/'.$this->model.$type.'/'.$this->model_id.'-'.$this->file_name.'.'.$this->format;

    }
}
