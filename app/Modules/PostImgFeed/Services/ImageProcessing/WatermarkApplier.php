<?php
// App\Modules\PostImgFeed\Services\ImageProcessing\WatermarkApplier.php
namespace App\Modules\PostImgFeed\Services\ImageProcessing;

use App\Modules\PostImgFeed\Services\ImageProcessing\Config\PositionYConfig;

class WatermarkApplier
{   

    public function applyFromFile($baseImage, string $watermarkPath, PositionYConfig $position): void
    {
        if (!file_exists($watermarkPath)) return;

        $watermark = imagecreatefrompng($watermarkPath);
        if (!$watermark) return;

        $imgWidth = imagesx($baseImage);
        $imgHeight = imagesy($baseImage);
        $wmWidth = imagesx($watermark);
        $wmHeight = imagesy($watermark);
        $x = intval(($imgWidth - $wmWidth) / 2);
        $y = $position->resolveY($imgHeight, $wmHeight);

        imagecopyresampled($baseImage, $watermark, $x, $y, 0, 0, $wmWidth, $wmHeight, $wmWidth, $wmHeight);
        imagedestroy($watermark);
    }
}
