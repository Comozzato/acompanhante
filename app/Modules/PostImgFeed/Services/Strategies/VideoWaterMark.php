<?php

declare(strict_types=1);

namespace App\Modules\PostImgFeed\Services\Strategies;

use App\Enums\ImagemFeed;
use App\Modules\PostImgFeed\Contracts\ImageWatermark;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFProbe;
use FFMpeg\Format\Video\X264;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use ProtoneMedia\LaravelFFMpeg\Filters\WatermarkFactory;
use ProtoneMedia\LaravelFFMpeg\MediaOpener;
use Intervention\Image\Facades\Image;

class VideoWaterMark implements ImageWatermark
{
    public function getSupportedType(): ImagemFeed
    {
        return ImagemFeed::VIDEO;
    }

    public function applyWatermark(UploadedFile $uploadedFile): string
    {
        // Caminho temporário para o upload
        try {
            [$relativeInputPath, $absoluteInputPath] = $this->salvarUploadTemporario($uploadedFile);
            // Caminho da imagem
            $relativeOutputPath = auth_user()->id . '/posts/video-' . uniqid() . '.mp4';

            // Processar vídeo com FFMpeg
            if (!Storage::disk('local')->exists($relativeInputPath)) {
                throw new \Exception("Arquivo não encontrado: $relativeInputPath");
            }
            $watermarkPath = public_path('watermarks/wmnovacolor24.png');
            if (!file_exists($watermarkPath)) {
                throw new \Exception("Arquivo de marca d'água não encontrado: $watermarkPath");
            }
            $paths = $this->criarVideoAplicarWaterMark($relativeInputPath, $relativeOutputPath);
        } finally {
            Storage::disk('local')->exists($absoluteInputPath) && unlink($absoluteInputPath);
        }
        return $paths;
    }


    private function salvarUploadTemporario(UploadedFile $uploadedFile): array
    {
        $relativeInputPath = 'tmp/' . uniqid('video_') . '.' . $uploadedFile->getClientOriginalExtension();
        $absoluteInputPath = storage_path('app/private/' . $relativeInputPath); // ⬅️ corrigido
        $uploadedFile->move(dirname($absoluteInputPath), basename($absoluteInputPath));
        return [$relativeInputPath, $absoluteInputPath];
    }

    private function criarVideoAplicarWaterMark($relativeInputPath, $relativeOutputPath): string
    {
        $video = FFMpeg::fromDisk('local')->open($relativeInputPath);
        $video = $this->waterMark($video, $relativeInputPath);
        $format = new X264('copy', 'libx264'); // mantém áudio original
        $format->setKiloBitrate(16000) // 0 para deixar CRF controlar a qualidade
            ->setAdditionalParameters([
                '-preset',
                'slow', // compressão eficiente
                '-crf',
                '18',      // alta qualidade
                '-c:a',
                'copy'     // mantém áudio original
            ]);
        // Exporta o vídeo com a marca d'água
        $video->export()
            ->toDisk('s3')  // salva no local
            ->inFormat($format)
            ->addFilter(function ($filters) {
                // 1. cortar até 60 segundos
                $filters->clip(\FFMpeg\Coordinate\TimeCode::fromSeconds(0), \FFMpeg\Coordinate\TimeCode::fromSeconds(60));
                // 2. redimensionar para no máximo 1080x1920
                //$filters->resize(new \FFMpeg\Coordinate\Dimension(1080, 1920), \FFMpeg\Filters\Video\ResizeFilter::RESIZEMODE_INSET, true);
            })
            ->save($relativeOutputPath);

        Storage::disk('s3')->setVisibility($relativeOutputPath, 'public');
        $thumbPath = $this->thumbnailFromExported($video);
        return json_encode([
            $relativeOutputPath,
            $thumbPath,
        ]);
    }

    private function waterMark(MediaOpener $video, $relativeInputPath): MediaOpener
    {
        [$wmf_path, $wmurl_path, $vwidth, $vheight] = $this->getWaterMarkPathForResolution($relativeInputPath);

        $wmf_dims = getimagesize(public_path($wmf_path));
        $wmf_width = $wmf_dims[0];
        $wmf_height = $wmf_dims[1];

        // 2. Obter as dimensões da segunda marca d'água (URL)
        $wmurl_dims = getimagesize(public_path($wmurl_path));
        $wmurl_width = $wmurl_dims[0];
        $wmurl_height = $wmurl_dims[1];
        $video->addWatermark(function (WatermarkFactory $watermark) use ($wmf_path, $wmurl_path, $vwidth, $vheight, $wmf_width, $wmf_height, $wmurl_width, $wmurl_height) {
            $watermark->fromDisk('public_root')
                ->open($wmf_path)
                ->left(intval(($vwidth / 2) - ($wmf_width / 2)))
                ->top(intval(($vheight / 2) - ($wmf_height / 2)));
        });
        $video->addWatermark(function (WatermarkFactory $watermark) use ($wmf_path, $wmurl_path, $vwidth, $vheight, $wmf_width, $wmf_height, $wmurl_width, $wmurl_height) {
            $offsetFromBottom = 30;
            $watermark->fromDisk('public_root')
                ->open($wmurl_path)
                ->bottom($offsetFromBottom)
                ->left(intval(($vwidth / 2) - ($wmurl_width / 2)));
        });
        return $video;
    }

    private function getWaterMarkPathForResolution($relativeInputPath)
    {
        $camtn = 'vdimages' . DIRECTORY_SEPARATOR;
        $ffprobe = FFProbe::create([
            'ffprobe.binaries' => env('FFPROBE_BINARIES'),
            'timeout'          => 3600,
            'ffmpeg.threads'   => 1,
        ]);
        $dimensions = $ffprobe->streams(storage_path('app/private/' . $relativeInputPath))->videos()->first()->getDimensions();
        $vwidth = $dimensions->getWidth();
        $vheight = $dimensions->getHeight();
        // array de larguras disponíveis
        $warr = [240, 426, 480, 640, 720, 854, 1080, 1280, 1920];

        // Encontrar a largura mais próxima que não exceda a largura do vídeo caso a largura do vídeo seja menor que a maior largura disponível
        if (!in_array($vwidth, $warr)) {
            $filtered = array_filter($warr, fn($w) => $w <= $vwidth);
            $vwidth = !empty($filtered) ? max($filtered) : min($warr);
        }

        // Caminho da marca d'água baseada na largura do vídeo
        $wmf_path = $camtn . $vwidth . '.png';
        $wmurl_path = $camtn . 'www' . $vwidth . '.png';

        return [$wmf_path, $wmurl_path, $vwidth, $vheight];
    }


    private function thumbnailFromExported(MediaOpener $video): string
    {
        $thumbnail_local_path = auth_user()->id . '/posts/video-thumbnail_' . uniqid() . '.png';
        $video->getFrameFromSeconds(1)
            ->export()
            ->toDisk('s3')
            ->save($thumbnail_local_path);
        return $thumbnail_local_path;
    }
}
