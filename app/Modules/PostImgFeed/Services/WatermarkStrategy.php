<?php
namespace App\Modules\PostImgFeed\Services;

use App\Modules\PostImgFeed\Services\Strategies\MasterImageWaterMark;
use App\Modules\Watermark\Contracts\ImageWatermark;
use App\Modules\Watermark\Services\Strategies\TypeImg;

class WatermarkStrategy
{
    public function resolve(TypeImg $mediaType): ImageWatermark 
    {
        return match ($mediaType) {
            TypeImg::Imagem_master => new MasterImageWaterMark(),
            // ImgTypeUploads::Thumbnail_prymary => new VideoWatermark(),
            // ImgTypeUploads::Thumbnail_secondary => new VideoWatermark(),
            default => throw new \InvalidArgumentException('Tipo de mídia não suportado para marca d\'água'),
        };
    }
}
