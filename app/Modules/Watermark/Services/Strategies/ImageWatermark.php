<?php
namespace App\Modules\Watermark\Services\Strategies;

use App\Modules\Watermark\Contracts\WatermarkServiceInterface;

class ImageWatermark implements WatermarkServiceInterface
{
    /**
     * Aplica marca d'água e redimensiona a imagem para o tamanho fixo de 'imgmalbum' (630x950, sem crop).
     *
     * @param string $inputFile Caminho da imagem original
     * @param string $watermark Caminho da marca d'água (PNG)
     * @param string|null $outputFile Caminho do arquivo de saída (opcional)
     * @return string Caminho do arquivo salvo
     */
    public function applyWatermark(
        string $inputFile,
        string $watermark,
        ?string $outputFile = null
    ): string {
        $width = 630;
        $height = 950;
        $crop = false; // Para imgmalbum, não corta

        if (!file_exists($inputFile) || !file_exists($watermark)) {
            throw new \InvalidArgumentException('Arquivo de imagem ou marca d\'água não encontrado.');
        }

        $imageInfo = getimagesize($inputFile);
        $mime = $imageInfo['mime'] ?? '';

        switch ($mime) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($inputFile);
                break;
            case 'image/png':
                $image = imagecreatefrompng($inputFile);
                break;
            default:
                throw new \InvalidArgumentException('Tipo de imagem não suportado.');
        }

        $origWidth = imagesx($image);
        $origHeight = imagesy($image);

        // Redimensiona para caber, sem cortar
        $scale = min($width / $origWidth, $height / $origHeight);
        $newWidth = intval($origWidth * $scale);
        $newHeight = intval($origHeight * $scale);
        $resized = imagecreatetruecolor($width, $height);
        // Fundo branco para JPEG
        if ($mime === 'image/jpeg') {
            $white = imagecolorallocate($resized, 255, 255, 255);
            imagefill($resized, 0, 0, $white);
        } else {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
        }
        // Centraliza a imagem redimensionada
        $dstX = intval(($width - $newWidth) / 2);
        $dstY = intval(($height - $newHeight) / 2);
        imagecopyresampled($resized, $image, $dstX, $dstY, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
        imagedestroy($image);
        $image = $resized;

        $wm = imagecreatefrompng($watermark);
        $wmWidth = imagesx($wm);
        $wmHeight = imagesy($wm);
        $imgWidth = imagesx($image);
        $imgHeight = imagesy($image);
        $destX = intval(($imgWidth / 2) - ($wmWidth / 2));
        $destY = intval(($imgHeight / 2) - ($wmHeight / 2));

        imagecopyresampled($image, $wm, $destX, $destY, 0, 0, $wmWidth, $wmHeight, $wmWidth, $wmHeight);

        $outputFile = $outputFile ?: $inputFile;
        $result = false;
        if ($mime === 'image/jpeg') {
            $result = imagejpeg($image, $outputFile, 100);
        } elseif ($mime === 'image/png') {
            $result = imagepng($image, $outputFile);
        }

        imagedestroy($image);
        imagedestroy($wm);

        if (!$result) {
            throw new \RuntimeException('Falha ao salvar a imagem com marca d\'água.');
        }

        return $outputFile;
    }
}
