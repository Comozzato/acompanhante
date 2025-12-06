<?php

namespace App\Modules\PostImgFeed\Services\Strategies;

use App\Enums\ImagemFeed;
use App\Modules\PostImgFeed\Contracts\ImageWatermark;
use App\Modules\PostImgFeed\Services\ImageProcessing\ImageResizer;
use App\Modules\PostImgFeed\Services\ImageProcessing\WatermarkApplier;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PrimaryImagemNoWaterMark implements ImageWatermark
{
    public function __construct(
        private ImageResizer $resizer,
        private WatermarkApplier $applier
    ) {}

    public function getSupportedType(): ImagemFeed
    {
        return ImagemFeed::NO_WATERMARK;
    }

    public function applyWatermark(UploadedFile $inputFile): string
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

        if ($imageInfo['mime'] === 'image/jpeg' && function_exists('exif_read_data')) {
            $exif = @exif_read_data($inputFile);
            if (!empty($exif['Orientation'])) {
                switch ($exif['Orientation']) {
                    case 3:
                        $image = imagerotate($image, 180, 0);
                        break;
                    case 6:
                        $image = imagerotate($image, -90, 0);
                        break;
                    case 8:
                        $image = imagerotate($image, 90, 0);
                        break;
                }
            }
        }

        if (!$image) {
            throw new \RuntimeException("Não foi possível criar a imagem a partir do arquivo: {$inputFile}");
        }
        imagedestroy($image);

        $filename = generate_unique_filename('sem_marca', pathinfo($inputFile, PATHINFO_FILENAME));
        $relativePath = auth_user()->id . '/posts/' . $filename;
        if (!file_exists($inputFile)) {
            throw new \RuntimeException("Arquivo não encontrado: {$inputFile}");
        }

        ob_start(); // inicia o buffer de saída
        imagepng($image); // renderiza imagem tratada no buffer
        $content = ob_get_clean(); // obtém o conteúdo e limpa o buffer

        if ($content === false || strlen($content) === 0) {
            throw new \RuntimeException("Arquivo inválido ou vazio: {$inputFile}");
        }

        Storage::disk('s3')->put($relativePath, $content);
        imagedestroy($image);
        return $relativePath;
    }
}
