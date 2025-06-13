<?php

namespace App\Modules\PostImgFeed\Services\Strategies;

use App\Enums\ImagesFeed;
use App\Modules\PostImgFeed\Contracts\ImageWatermark;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MasterImageWaterMark implements ImageWatermark
{
    private const WIDTH = 630;
    private const HEIGHT = 950;

    public function getSupportedType(): ImagesFeed
    {
        return ImagesFeed::MASTER;
    }

    /**
     * Aplica as marcas d'água e redimensiona a imagem.
     *
     * @param string $inputFile
     * @param string|null $outputFile
     * @return string
     * @throws \RuntimeException
     */
    public function applyWatermark($inputFile): string
    {
        $imageInfo = getimagesize($inputFile);
        if (!$imageInfo) {
            throw new \RuntimeException("Não foi possível obter informações da imagem: {$inputFile}");
        }
        $mime = $imageInfo['mime'];

        switch ($mime) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($inputFile);
                break;
            case 'image/png':
                $image = imagecreatefrompng($inputFile);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($inputFile);
                break;
            default:
                throw new \RuntimeException("Tipo de imagem não suportado: {$mime}");
        }
        if (!$image) {
            throw new \RuntimeException("Falha ao carregar imagem: {$inputFile}");
        }
        $resized = $this->resizeWithWhiteBackground($image, self::WIDTH, self::HEIGHT);
        imagedestroy($image);

        // Aplica marcas d'água
        $this->applyWatermarkFromFile($resized, public_path('watermarks/wmnovacolor24.png'), 'middle');
        $this->applyWatermarkFromFile($resized, public_path('watermarks/mctop24.png'), 'top');
        $this->applyWatermarkFromFile($resized, public_path('watermarks/wmnovaurl24.png'), 'bottom');
        $filename = pathinfo($inputFile, PATHINFO_FILENAME) . '.png';
        $relativePath = 'watermarked/' . $filename;
        if (!Storage::exists('watermarked')) {
            Storage::makeDirectory('watermarked');
        }

        $finalPath = Storage::path($relativePath);
        if (!imagepng($resized, $finalPath)) {
            imagedestroy($resized);
            throw new \RuntimeException("Falha ao salvar imagem: {$finalPath}");
        }

        imagedestroy($resized);
        return $finalPath;
    }

    /**
     * Redimensiona a imagem com fundo branco mantendo proporção.
     *
     * @param resource $image
     * @param int $targetWidth
     * @param int $targetHeight
     * @return resource
     * @throws \RuntimeException
     */
    private function resizeWithWhiteBackground($image, int $targetWidth, int $targetHeight)
    {
        $origWidth = imagesx($image);
        $origHeight = imagesy($image);

        $scale = min($targetWidth / $origWidth, $targetHeight / $origHeight);
        $newWidth = intval($origWidth * $scale);
        $newHeight = intval($origHeight * $scale);

        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
        if (!$canvas) {
            throw new \RuntimeException('Erro ao criar canvas de redimensionamento.');
        }

        $white = imagecolorallocate($canvas, 255, 255, 255);
        if ($white === false) {
            imagedestroy($canvas);
            throw new \RuntimeException('Erro ao alocar cor branca.');
        }

        imagefill($canvas, 0, 0, $white);

        $dstX = intval(($targetWidth - $newWidth) / 2);
        $dstY = intval(($targetHeight - $newHeight) / 2);

        if (!imagecopyresampled($canvas, $image, $dstX, $dstY, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight)) {
            imagedestroy($canvas);
            throw new \RuntimeException('Erro ao redimensionar imagem.');
        }

        return $canvas;
    }

    /**
     * Aplica marca d'água em uma posição específica.
     *
     * @param resource $baseImage
     * @param string $watermarkPath
     * @param string $position (top, middle, bottom)
     */
    private function applyWatermarkFromFile($baseImage, string $watermarkPath, string $position): void
    {
        if (!file_exists($watermarkPath)) {
            error_log("Marca d'água não encontrada: {$watermarkPath}");
            return;
        }

        $watermark = imagecreatefrompng($watermarkPath);
        if (!$watermark) {
            error_log("Erro ao carregar marca d'água: {$watermarkPath}");
            return;
        }

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
