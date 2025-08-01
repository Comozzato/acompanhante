<?php

declare(strict_types=1);

namespace App\Modules\PostImgFeed\Services\Strategies;

use App\Enums\ImagemFeed;
use App\Modules\PostImgFeed\Contracts\ImageWatermark;
use Illuminate\Http\UploadedFile;
use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\X264;
use ProtoneMedia\LaravelFFMpeg\Filters\WatermarkFactory;

class VideoWaterMark implements ImageWatermark
{
    public function getSupportedType(): ImagemFeed
    {
        return ImagemFeed::VIDEO;
    }

    public function applyWatermark(UploadedFile $uploadedFile): string
    {
        // 1. Mover o vídeo de upload para um local temporário
        $tempInputPath = storage_path('app/tmp/' . uniqid('video_') . '.' . $uploadedFile->getClientOriginalExtension());
        $uploadedFile->move(dirname($tempInputPath), basename($tempInputPath));

        // 2. Definir os caminhos de forma limpa
        $imagePath = public_path('watermarks/wmnovacolor24.png');
        $outputFilename = 'video-' . uniqid() . '.mp4';
        $relativePath = auth_user()->id . '/posts/' . $outputFilename;
        $absoluteOutputPath = storage_path('app/tmp/' . $outputFilename);
        $filterScriptPath = storage_path('app/tmp/filter_script_' . uniqid() . '.txt');

        FFMpeg::fromDisk('local')
            ->open($tempInputPath)
            ->addFilter(function (WatermarkFactory $watermark) {
                $watermark->fromDisk('public_root') // ou 'local', se a imagem estiver no storage
                    ->open('watermarks/wmnovacolor24.png')
                    ->right(10)
                    ->bottom(10);
            })
            ->export()
            ->toDisk('s3')
            ->inFormat(new X264('aac', 'libx264'))
            ->save($relativePath);
        // 8. Salvar o vídeo
        // 9. Subir para S3 e limpar TODOS os arquivos temporários

        unlink($tempInputPath);
        unlink($absoluteOutputPath);
        unlink($filterScriptPath);

        return $relativePath;
    }

    /**
     * Aplica o filtro de marca d'água de forma robusta para o ambiente Windows.
     */
    // private function waterMark(Video $video): Video
    // {
    //     $video->
    //     return $video;
    // }
}
