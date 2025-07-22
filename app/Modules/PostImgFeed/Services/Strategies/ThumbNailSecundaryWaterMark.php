<?php

namespace App\Modules\PostImgFeed\Services\Strategies;

use App\Enums\ImagemFeed;
use App\Modules\PostImgFeed\Contracts\ImageWatermark;
use App\Modules\PostImgFeed\Services\ImageProcessing\ImageResizer;
use App\Modules\PostImgFeed\Services\ImageProcessing\WatermarkApplier;
use App\Modules\PostImgFeed\Services\ImageProcessing\Config\ImageResizeConfig;
use Illuminate\Support\Facades\Storage;

class ThumbNailSecundaryWaterMark implements ImageWatermark
{
    public function __construct(
        private ImageResizer $resizer,
        private WatermarkApplier $applier
    ) {}

    public function getSupportedType(): ImagemFeed
    {   
        return ImagemFeed::THBSECUNDARY;
    }

    public function applyWatermark($inputFile): string
    {
        $imageInfo = getimagesize($inputFile);
        if (!$imageInfo) {
            throw new \RuntimeException("Não foi possível obter informações da imagem: {$inputFile}");
        }

        $image = match ($imageInfo['mime']) {
            'image/jpeg' => imagecreatefromjpeg($inputFile),
            'image/png' => imagecreatefrompng($inputFile),
            'image/gif' => imagecreatefromgif($inputFile),
            default => throw new \RuntimeException("Tipo de imagem não suportado: {$imageInfo['mime']}"),
        };

        $resized = $this->resizer->resizeWithWhiteBackground($image, new ImageResizeConfig(390, 585,true));
        imagedestroy($image);

        $this->applier->applyFromFile($resized, public_path('watermarks/wmnovacolor24.png'), 'middle');
        $this->applier->applyFromFile($resized, public_path('watermarks/mctop24.png'), 'top');
        $this->applier->applyFromFile($resized, public_path('watermarks/wmnovaurl24.png'), 'bottom');

        $filename =  'secundary_'. pathinfo($inputFile, PATHINFO_FILENAME) . '.png';
        $relativePath = 'watermarked/' . $filename;
        Storage::makeDirectory('watermarked');
        $finalPath = Storage::path($relativePath);

        if (!imagepng($resized, $finalPath)) {
            imagedestroy($resized);
            throw new \RuntimeException("Falha ao salvar imagem: {$finalPath}");
        }

        imagedestroy($resized);
        return $finalPath;
    }
}
