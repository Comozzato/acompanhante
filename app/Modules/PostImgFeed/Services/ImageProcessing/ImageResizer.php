<?php
// App\Modules\PostImgFeed\Services\ImageProcessing\ImageResizer.php
namespace App\Modules\PostImgFeed\Services\ImageProcessing;

use App\Modules\PostImgFeed\Services\ImageProcessing\Config\ImageResizeConfig;

class ImageResizer
{
    public function resizeWithBlackBackground($image, ImageResizeConfig $config)
    {
        $origWidth = imagesx($image);
        $origHeight = imagesy($image);

        $scale = max($config->width / $origWidth, $config->height / $origHeight);
        $newWidth = intval($origWidth * $scale);
        $newHeight = intval($origHeight * $scale);

        $canvas = imagecreatetruecolor($config->width, $config->height);
        // $black = imagecolorallocate($canvas, 0, 0, 0);
        // imagefill($canvas, 0, 0, $black);
        $dstX = intval(($config->width - $newWidth) / 2);
        $dstY = intval(($config->height - $newHeight) / 2);

        imagecopyresampled($canvas, $image, $dstX, $dstY, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

        return $canvas;
    }

    public function resizeFillWithoutCrop($image, ImageResizeConfig $config)
    {
        $origWidth = imagesx($image);
        $origHeight = imagesy($image);

        $scale = min($config->width / $origWidth, $config->height / $origHeight);
        $newWidth = intval($origWidth * $scale);
        $newHeight = intval($origHeight * $scale);

        $canvas = imagecreatetruecolor($config->width, $config->height);
        imagesavealpha($canvas, true);
        $transparency = imagecolorallocatealpha($canvas, 255, 255, 255, 127);
        imagefill($canvas, 0, 0, $transparency);
        $dstX = intval(($config->width - $newWidth) / 2);
        $dstY = intval(($config->height - $newHeight) / 2);
        imagecopyresampled($canvas, $image, $dstX, $dstY, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
        return $canvas;
    }
}
