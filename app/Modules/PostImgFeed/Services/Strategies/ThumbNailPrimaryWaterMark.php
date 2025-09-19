<?php

namespace App\Modules\PostImgFeed\Services\Strategies;

use App\Enums\ImagemFeed;
use App\Modules\PostImgFeed\Contracts\ImageWatermark;
use App\Modules\PostImgFeed\Services\ImageProcessing\ImageResizer;
use App\Modules\PostImgFeed\Services\ImageProcessing\WatermarkApplier;
use App\Modules\PostImgFeed\Services\ImageProcessing\Config\ImageResizeConfig;
use App\Modules\PostImgFeed\Services\ImageProcessing\Config\PositionConfig;
use App\Modules\PostImgFeed\Services\ImageProcessing\Config\PositionXConfig;
use App\Modules\PostImgFeed\Services\ImageProcessing\Config\PositionYConfig;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ThumbNailPrimaryWaterMark implements ImageWatermark
{
    public function __construct(
        private ImageResizer $resizer,
        private WatermarkApplier $applier
    ) {}

    public function getSupportedType(): ImagemFeed
    {
        return ImagemFeed::THBPRIMARY;
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

        $resized = $this->resizer->resizeWithBlackBackground($image, new ImageResizeConfig(620, 930, true));
        imagedestroy($image);

     
        $this->applier->applyFromFile($resized, public_path('watermarks/wmnovacolor24.png'), new PositionConfig(
            new PositionXConfig('center'),
            new PositionYConfig('middle')
        ));
        $this->applier->applyFromFile($resized, public_path('watermarks/mctop24.png'), new PositionConfig(
            new PositionXConfig('left'),
            new PositionYConfig('top')
        ));
        $this->applier->applyFromFile($resized, public_path('watermarks/wmnovaurl24.png'), new PositionConfig(
            new PositionXConfig('center'),
            new PositionYConfig('bottom')
        ));

        $filename = generate_unique_filename('primary', pathinfo($inputFile, PATHINFO_FILENAME));
        $relativePath = auth_user()->id . '/posts/' . $filename;
        if (!file_exists($inputFile)) {
            throw new \RuntimeException("Arquivo não encontrado: {$inputFile}");
        }

        ob_start(); // inicia o buffer de saída
        imagepng($resized); // renderiza imagem tratada no buffer
        $content = ob_get_clean(); // obtém o conteúdo e limpa o buffer

        if ($content === false || strlen($content) === 0) {
            throw new \RuntimeException("Arquivo inválido ou vazio: {$inputFile}");
        }

        Storage::disk('s3')->put($relativePath, $content);
        imagedestroy($resized);

        return $relativePath;
    }
}
