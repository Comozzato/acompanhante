<?php
// App\Modules\PostImgFeed\Services\ImageProcessing\ImageResizer.php
namespace App\Modules\PostImgFeed\Services\ImageProcessing;

use App\Modules\PostImgFeed\Services\ImageProcessing\Config\ImageResizeConfig;
use Illuminate\Support\Facades\Storage;

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

    public function resizeFillWithoutCrop($imageOrigem, ImageResizeConfig $config)
    {
        $larguraOrigem = imagesx($imageOrigem);
        $alturaOrigem  = imagesy($imageOrigem);

        // Detecta se a imagem é horizontal ou vertical
        if ($larguraOrigem >= $alturaOrigem) {
            // Imagem horizontal → ajusta pela largura
            $escala = $config->width / $larguraOrigem;
        } else {
            // Imagem vertical → ajusta pela altura
            $escala = $config->height / $alturaOrigem;
        }

        $novaLargura = (int) ($larguraOrigem * $escala);
        $novaAltura  = (int) ($alturaOrigem * $escala);

        // cria imagem final do tamanho proporcional (sem canvas forçado)
        $imagemFinal = imagecreatetruecolor($novaLargura, $novaAltura);

        // fundo branco (caso queira manter PNG transparente, trocar por transparente)
        $branco = imagecolorallocate($imagemFinal, 255, 255, 255);
        imagefill($imagemFinal, 0, 0, $branco);

        // copia redimensionando
        imagecopyresampled(
            $imagemFinal,
            $imageOrigem,
            0,
            0, // sem centralizar, ocupa tudo
            0,
            0,
            $novaLargura,
            $novaAltura,
            $larguraOrigem,
            $alturaOrigem
        );
        imagedestroy($imageOrigem);
        return $imagemFinal;
    }
}
