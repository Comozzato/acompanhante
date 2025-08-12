<?php

namespace App\Models;

use App\Services\S3ImageGalleryService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Midia extends Model
{
    //
    protected $table = 'midia';

    protected $fillable = ['feed_id', 'midia'];

    protected $appends = ['url','tipo'];

    public function getUrlAttribute()
    {
        return S3ImageGalleryService::getImage($this->midia);
    }

    public function getTipoAttribute()
    {
        $ext = pathinfo($this->midia, PATHINFO_EXTENSION);
        return match ($ext) {
            'jpg', 'jpeg', 'png', 'gif' => 'image',
            'mp4', 'avi', 'mov' => 'video',
            default => 'unknown',
        };
    }
    public function deleteFile()
    {   
        S3ImageGalleryService::deleteImage($this->midia);
    }
}
