<?php

declare(strict_types=1);

namespace App\Modules\PostImgFeed\Services\Strategies;

use App\Enums\ImagemFeed;
use App\Modules\PostImgFeed\Contracts\ImageWatermark;
use FFMpeg\FFProbe;
use FFMpeg\Filters\Video\VideoFilters;
use FFMpeg\Format\Video\X264;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use ProtoneMedia\LaravelFFMpeg\Filters\WatermarkFactory;

class VideoWaterMark implements ImageWatermark
{

    private $ffprobe;

    public function __construct(private ImageManager $imageManager)
    {
        $this->initializeFFMpeg();
    }

    private function initializeFFMpeg(): void
    {
        if (!$this->ffprobe) {
            $this->ffprobe = FFProbe::create([
                'ffprobe.binaries' => env('FFPROBE_BINARIES'),
                'timeout'          => 3600,
                'ffmpeg.threads'   => 1,
            ]);
        }
    }
    
    public function getSupportedType(): ImagemFeed
    {
        return ImagemFeed::VIDEO;
    }

    public function applyWatermark(UploadedFile $uploadedFile): string
    {

        try {
            // 3. Processa o vídeo
            $paths = $this->processarVideo($uploadedFile);
            return $paths;
        } catch (\Exception $e) {
            throw new \Exception("Erro ao processar vídeo: " . $e->getMessage());
        } finally {
            FFMpeg::cleanupTemporaryFiles();
        }
    }

    private function processarVideo($uploadedFile): string
    {

        $video = FFMpeg::open($uploadedFile);
        info('video aberto com sucesso com o laravel ffmpeg');
        [$watermarkPaths, $dimensions] = $this->getVideoDimensionsAndWatermarks($uploadedFile);
        info('dimensoes coletadas com sucessos: ' . json_encode($dimensions));
        // Aplica watermarks antes de exportar
        $this->applyWatermarkToVideo($video, $watermarkPaths, $dimensions);
        info('marcas da agua aplicado com sucesso');
        return $this->exportVideo($video);
    }

    private function exportVideo($video): string
    {
        $relativeOutputPath = auth_user()->id . '/posts/video-' . uniqid() . '.mp4';

        $thumbnailPath = auth_user()->id . '/posts/video-thumbnail_' . uniqid() . '.png';
        $format = (new X264('aac', 'libx264'))
            ->setAdditionalParameters([
                '-preset',
                'slow',
                '-crf',
                '20' // melhor que fixar bitrate alto
            ]);


        $video->export()
            ->toDisk('local')
            ->inFormat($format)
            ->save($relativeOutputPath);

        $videoLocal = FFMpeg::fromDisk('local')
            ->open($relativeOutputPath)
            ->addFilter(function (VideoFilters $filters) {
                $filters->resize(new \FFMpeg\Coordinate\Dimension(480, 853));
            });

        $frame = $videoLocal->getFrameFromSeconds(1);
        $frame->export()->toDisk('local')->save($thumbnailPath);
        $thumbAbsolutePath = Storage::disk('local')->path($thumbnailPath);

        $image = $this->imageManager->read($thumbAbsolutePath);
        $image->scale(480, 853);
        $image->toPng()->save($thumbAbsolutePath);

        Storage::disk('s3')->put($relativeOutputPath, Storage::disk('local')->get($relativeOutputPath));
        Storage::disk('s3')->put($thumbnailPath, Storage::disk('local')->get($thumbnailPath));
        Storage::disk('local')->delete($relativeOutputPath);
        Storage::disk('local')->delete($thumbnailPath);
        return json_encode([$relativeOutputPath, $thumbnailPath]);
    }

    private function applyWatermarkToVideo($video, array $watermarkPaths, array $dimensions): void
    {
        $vwidth = $dimensions['width'];
        $vheight = $dimensions['height'];

        // Carrega dimensões dos watermarks UMA VEZ
        $mainWatermarkSize = getimagesize(public_path($watermarkPaths['main']));
        $urlWatermarkSize = getimagesize(public_path($watermarkPaths['url']));

        // Aplica watermarks em lote
        $video->addWatermark(function (WatermarkFactory $watermark) use ($watermarkPaths, $vwidth, $vheight, $mainWatermarkSize) {
            $watermark->fromDisk('public_root')
                ->open($watermarkPaths['main'])
                ->left(intval(($vwidth / 2) - ($mainWatermarkSize[0] / 2)))
                ->top(intval(($vheight / 2) - ($mainWatermarkSize[1] / 2)));
        });
        info('marca da agua principal aplicado');
        $video->addWatermark(function (WatermarkFactory $watermark) use ($watermarkPaths, $vwidth, $vheight, $urlWatermarkSize) {
            $watermark->fromDisk('public_root')
                ->open($watermarkPaths['url'])
                ->bottom(30)
                ->left(intval(($vwidth / 2) - ($urlWatermarkSize[0] / 2)));
        });
        info('marca da agua url aplicado');
    }

    private function getVideoDimensionsAndWatermarks($uploadedFile): array
    {
        $tempPath = $uploadedFile->getRealPath();
        $videoStream = $this->ffprobe->streams($tempPath)->videos()->first();
        $dimensions = $videoStream->getDimensions();
        $vwidth = $dimensions->getWidth();
        $vheight = $dimensions->getHeight();
        // Encontra a resolução mais adequada
        $optimalWidth = $this->findOptimalWatermarkResolution($vwidth);
        $watermarkPaths = $this->getWatermarkPaths($optimalWidth);
        return [
            $watermarkPaths,
            ['width' => $vwidth, 'height' => $vheight, 'optimal_width' => $optimalWidth]
        ];
    }

    private function findOptimalWatermarkResolution(int $videoWidth): int
    {
        $supportedResolutions = [240, 426, 480, 640, 720, 854, 1080, 1280, 1920];

        $filtered = array_filter($supportedResolutions, fn($w) => $w <= $videoWidth);
        return !empty($filtered) ? max($filtered) : min($supportedResolutions);
    }


    private function getWatermarkPaths(int $width): array
    {
        $basePath = 'vdimages' . DIRECTORY_SEPARATOR;
        $wmfPath = $basePath . $width . '.png';
        $wmurlPath = $basePath . 'www' . $width . '.png';
        if (!file_exists(public_path($wmfPath)) || !file_exists(public_path($wmurlPath))) {
            throw new \RuntimeException("Watermarks não encontrados para resolução: {$width}px");
        }
        return ['main' => $wmfPath, 'url' => $wmurlPath];
    }
}
