<?php

declare(strict_types=1);

namespace App\Modules\PostImgFeed\Services\Strategies;

use App\Enums\ImagemFeed;
use App\Modules\PostImgFeed\Contracts\ImageWatermark;
use FFMpeg\Format\Video\X264 as VideoX264;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use ProtoneMedia\LaravelFFMpeg\Exporters\X264;
use ProtoneMedia\LaravelFFMpeg\Filters\WatermarkFactory;

class VideoWaterMark implements ImageWatermark
{
    public function getSupportedType(): ImagemFeed
    {
        return ImagemFeed::VIDEO;
    }

    public function applyWatermark(UploadedFile $uploadedFile): string
    {
        // Caminhos
        // dd([
        //     'tamanho' => $uploadedFile->getSize(),
        //     'extensão' => $uploadedFile->getClientOriginalExtension(),
        //     'mime' => $uploadedFile->getMimeType(),
        // ]);
        $relativeInputPath = 'tmp/' . uniqid('video_') . '.' . $uploadedFile->getClientOriginalExtension();
        $absoluteInputPath = storage_path('app/private/' . $relativeInputPath); // ⬅️ corrigido

        $uploadedFile->move(dirname($absoluteInputPath), basename($absoluteInputPath));
        // Caminho da imagem
        $relativeOutputPath = auth_user()->id . '/posts/video-' . uniqid() . '.mp4';
        // Processar vídeo com FFMpeg
        if (!Storage::disk('local')->exists($relativeInputPath)) {
            throw new \Exception("Arquivo não encontrado: $relativeInputPath");
        }
        $watermarkPath = public_path('watermarks\\wmnovacolor24.png');
        if (!file_exists($watermarkPath)) {
            throw new \Exception("Arquivo de marca d'água não encontrado: $watermarkPath");
        }
        FFMpeg::fromDisk('local')
            ->open($relativeInputPath)
            ->addWatermark(function (WatermarkFactory $watermark) {
                $watermark->fromDisk('public_root')
                    ->open('watermarks/wmnovacolor24.png')
                    ->top(25)
                    ->bottom(25)
                    ->left(25)
                    ->right(25);
            })
            ->export()
            ->toDisk('s3')  // salva no local
            ->inFormat(new VideoX264('aac', 'libx264'))
            ->save($relativeOutputPath);

        // Limpar arquivo temporário
        unlink($absoluteInputPath);

        return $relativeOutputPath;
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
