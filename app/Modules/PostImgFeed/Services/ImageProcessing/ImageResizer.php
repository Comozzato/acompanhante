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

        // Escala proporcional: preenche largura ou altura SEM cortar
        $scale = min($config->width / $origWidth, $config->height / $origHeight);

        $newWidth = (int)($origWidth * $scale);
        $newHeight = (int)($origHeight * $scale);

        // Cria canvas exatamente no tamanho desejado
        $canvas = imagecreatetruecolor($config->width, $config->height);

        // Preserva transparência
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefill($canvas, 0, 0, $transparent);

        // Centraliza a imagem no canvas
        $dstX = (int)(($config->width - $newWidth) / 2);
        $dstY = (int)(($config->height - $newHeight) / 2);

        imagecopyresampled(
            $canvas,
            $image,
            $dstX,
            $dstY,
            0,
            0,
            $newWidth,
            $newHeight,
            $origWidth,
            $origHeight
        );

        return $canvas;
    }
}
