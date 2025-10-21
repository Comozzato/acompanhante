<?php

namespace App\Models;

use App\Services\S3ImageGalleryService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Midia extends Model implements Auditable
{
    //
    use AuditableTrait;

    protected $table = 'midia';

    protected $fillable = ['feed_id', 'midia'];

    protected $appends = ['url', 'tipo'];

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

    public function scopeOfTipo($query, $tipo)
    {
        $extensoes = [
            'imagem' => ['jpg', 'jpeg', 'png', 'gif'],
            'video'  => ['mp4', 'avi', 'mov'],
        ];

        if (!isset($extensoes[$tipo])) {
            return $query;
        }

        return $query->where(function ($q) use ($extensoes, $tipo) {
            foreach ($extensoes[$tipo] as $ext) {
                $q->orWhere('midia', 'ILIKE', "%.$ext");
            }
        });
    }
}
