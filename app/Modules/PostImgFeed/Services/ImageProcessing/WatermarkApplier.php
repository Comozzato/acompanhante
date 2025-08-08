<?php
// App\Modules\PostImgFeed\Services\ImageProcessing\WatermarkApplier.php
namespace App\Modules\PostImgFeed\Services\ImageProcessing;

use App\Modules\PostImgFeed\Services\ImageProcessing\Config\PositionConfig;
use App\Modules\PostImgFeed\Services\ImageProcessing\Config\PositionYConfig;

class WatermarkApplier
{

    public function applyFromFile($baseImage, string $watermarkPath, PositionConfig $position): void
    {
        if (!file_exists($watermarkPath)) return;

        $watermark = imagecreatefrompng($watermarkPath);
        if (!$watermark) return;

        $imgWidth = imagesx($baseImage);
        $imgHeight = imagesy($baseImage);
        $wmWidth = imagesx($watermark);
        $wmHeight = imagesy($watermark);

        // MODIFICAÇÃO AQUI: Calcule X e Y usando o objeto de configuração
        $x = $position->x->resolveX($imgWidth, $wmWidth);
        $y = $position->y->resolveY($imgHeight, $wmHeight);

        imagecopyresampled($baseImage, $watermark, $x, $y, 0, 0, $wmWidth, $wmHeight, $wmWidth, $wmHeight);
        imagedestroy($watermark);
    }
}
