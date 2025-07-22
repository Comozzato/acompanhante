<?php
// App\Modules\PostImgFeed\Services\ImageProcessing\WatermarkApplier.php
namespace App\Modules\PostImgFeed\Services\ImageProcessing;

class WatermarkApplier
{
    public function applyFromFile($baseImage, string $watermarkPath, string $position = 'middle'): void
    {
        if (!file_exists($watermarkPath)) return;

        $watermark = imagecreatefrompng($watermarkPath);
        if (!$watermark) return;

        $imgWidth = imagesx($baseImage);
        $imgHeight = imagesy($baseImage);
        $wmWidth = imagesx($watermark);
        $wmHeight = imagesy($watermark);

        $x = intval(($imgWidth - $wmWidth) / 2);
        $padding = 10;
        $y = match ($position) {
            'top'    => $padding,
            'middle' => intval(($imgHeight - $wmHeight) / 2),
            'bottom' => $imgHeight - $wmHeight - $padding,
            default  => intval(($imgHeight - $wmHeight) / 2),
        };
        
        imagecopyresampled($baseImage, $watermark, $x, $y, 0, 0, $wmWidth, $wmHeight, $wmWidth, $wmHeight);
        imagedestroy($watermark);
    }
}
