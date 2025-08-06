<?php

declare(strict_types=1);

namespace App\Modules\PostImgFeed\Services\Strategies;

use App\Enums\ImagemFeed;
use App\Modules\PostImgFeed\Contracts\ImageWatermark;
use FFMpeg\Format\Video\X264;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use ProtoneMedia\LaravelFFMpeg\Filters\WatermarkFactory;
use ProtoneMedia\LaravelFFMpeg\MediaOpener;

class VideoWaterMark implements ImageWatermark
{
    public function getSupportedType(): ImagemFeed
    {
        return ImagemFeed::VIDEO;
    }

    public function applyWatermark(UploadedFile $uploadedFile): string
    {
        // Caminho temporário para o upload
        [$relativeInputPath, $absoluteInputPath] = $this->salvarUploadTemporario($uploadedFile);
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
        $this->criarVideoAplicarWaterMark($relativeInputPath, $relativeOutputPath);
        // Limpar arquivo temporário
        Storage::disk('local')->exists($absoluteInputPath) && unlink($absoluteInputPath);

        return $relativeOutputPath;
    }


    private function salvarUploadTemporario(UploadedFile $uploadedFile): array
    {
        $relativeInputPath = 'tmp/' . uniqid('video_') . '.' . $uploadedFile->getClientOriginalExtension();
        $absoluteInputPath = storage_path('app/private/' . $relativeInputPath); // ⬅️ corrigido
        $uploadedFile->move(dirname($absoluteInputPath), basename($absoluteInputPath));
        return [$relativeInputPath, $absoluteInputPath];
    }
    
    private function criarVideoAplicarWaterMark($relativeInputPath, $relativeOutputPath)
    {
        $video = FFMpeg::fromDisk('local')->open($relativeInputPath);

        $video = $this->waterMark($video);

        $video->export()
            ->toDisk('s3')  // salva no local
            ->inFormat(new X264('aac', 'libx264'))
            ->save($relativeOutputPath);
    }

    private function waterMark(MediaOpener $video): MediaOpener
    {
        return $video->addWatermark(function (WatermarkFactory $watermark) {
            $watermark->fromDisk('public_root')
                ->open('watermarks/wmnovacolor24.png')
                ->top(25)
                ->bottom(25)
                ->left(25)
                ->right(25);
        });
    }
}
