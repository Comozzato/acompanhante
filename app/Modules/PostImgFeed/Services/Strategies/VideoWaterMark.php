<?php

declare(strict_types=1);

namespace App\Modules\PostImgFeed\Services\Strategies;

use App\Enums\ImagemFeed;
use App\Modules\PostImgFeed\Contracts\ImageWatermark;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\FFProbe;
use FFMpeg\Format\Video\X264;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use FFMpeg\FFMpeg;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg as LaravelFFMpeg;
use ProtoneMedia\LaravelFFMpeg\Filters\WatermarkFactory;

class VideoWaterMark implements ImageWatermark
{
    private $ffmpeg;
    private $ffprobe;

    public function __construct()
    {
        $this->initializeFFMpeg();
    }

    private function initializeFFMpeg(): void
    {
        if (!$this->ffmpeg) {
            $this->ffmpeg = FFMpeg::create([
                'ffmpeg.binaries'  => env('FFMPEG_BINARIES'),
                'ffprobe.binaries' => env('FFPROBE_BINARIES'),
                'timeout'          => 3600,
                'ffmpeg.threads'   => 1,
            ]);
        }

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
        // 1. Salva upload temporário
        [$relativeInputPath, $absoluteInputPath] = $this->salvarUploadTemporario($uploadedFile);

        // 2. Define caminho de saída
        $relativeOutputPath = $this->generateOutputPath();

        try {
            // 3. Processa o vídeo
            $paths = $this->processarVideo($relativeInputPath, $relativeOutputPath);

            // 4. Valida o resultado
            $this->validarArquivoProcessado($relativeOutputPath);

            return $paths;
        } catch (\Exception $e) {
            throw new \Exception("Erro ao processar vídeo: " . $e->getMessage());
        } finally {
            // 5. Limpa arquivos temporários
            $this->limparArquivosTemporarios($relativeInputPath, $absoluteInputPath);
        }
    }

    private function processarVideo(string $relativeInputPath, string $relativeOutputPath): string
    {
        info("Processando vídeo: {$relativeInputPath} -> {$relativeOutputPath}");

        [$watermarkPaths, $dimensions] = $this->getVideoDimensionsAndWatermarks($relativeInputPath);

        $video = LaravelFFMpeg::fromDisk('local')->open($relativeInputPath);

        // Aplica watermarks antes de exportar
        $this->applyWatermarkToVideo($video, $watermarkPaths, $dimensions);

        // Exporta vídeo processado para disco local
        $this->exportVideo($video, $relativeOutputPath);

        // Gera thumbnail já com filtros aplicados
        $thumbPath = $this->generateThumbnailWithFilters($relativeOutputPath);
        info("Thumbnail gerada: {$thumbPath}");
        // Upload final para S3 (vídeo + thumb)

        return json_encode([$relativeOutputPath, $thumbPath]);
    }

    private function exportVideo($video, string $tempPath): void
    {
        $format = (new X264('aac', 'libx264'))
            ->setAdditionalParameters([
                '-preset',
                'slow',
                '-crf',
                '20' // melhor que fixar bitrate alto
            ]);
        $video->export()
            ->toDisk('s3')
            ->inFormat($format)
            ->save($tempPath);
        $video->export()
            ->toDisk('local')
            ->inFormat($format)
            ->save($tempPath);

        info("Vídeo exportado com sucesso: {$tempPath}");
    }

    private function generateThumbnailWithFilters($relativeOutputPath): string
    {
        $video = LaravelFFMpeg::fromDisk('local', $this->ffmpeg)->open($relativeOutputPath);
        $thumbnailPath = auth_user()->id . '/posts/video-thumbnail_' . uniqid() . '.png';

        // captura o frame do vídeo já com watermarks aplicados
        $video->getFrameFromSeconds(1)
            ->export()
            ->toDisk('s3')
            ->save($thumbnailPath);

        info("Thumbnail gerada: {$thumbnailPath}");

        if (Storage::disk('local')->exists($relativeOutputPath)) {
            Storage::disk('local')->delete($relativeOutputPath);
        }
        return $thumbnailPath;
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

        $video->addWatermark(function (WatermarkFactory $watermark) use ($watermarkPaths, $vwidth, $vheight, $urlWatermarkSize) {
            $watermark->fromDisk('public_root')
                ->open($watermarkPaths['url'])
                ->bottom(30)
                ->left(intval(($vwidth / 2) - ($urlWatermarkSize[0] / 2)));
        });
    }

    private function getVideoDimensionsAndWatermarks(string $relativeInputPath): array
    {
        $fullPath = Storage::disk('local')->path($relativeInputPath);

        if (!file_exists($fullPath)) {
            throw new \RuntimeException("Arquivo não encontrado: {$fullPath}");
        }

        $stream = $this->ffprobe->streams($fullPath)->videos()->first();
        if (!$stream) {
            throw new \RuntimeException("Não foi possível obter informações do vídeo: {$fullPath}");
        }

        $dimensions = $stream->getDimensions();
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

    private function generateOutputPath(): string
    {
        return auth_user()->id . '/posts/video-' . uniqid() . '.mp4';
    }

    private function validarArquivoProcessado(string $path): void
    {
        if (!Storage::disk('s3')->exists($path)) {
            throw new \Exception("Arquivo processado não encontrado: {$path}");
        }
        $fileSize = Storage::disk('s3')->size($path);
        if ($fileSize <= 0) {
            throw new \Exception("Arquivo processado está vazio: {$path}");
        }
    }

    private function salvarUploadTemporario(UploadedFile $uploadedFile): array
    {
        try {
            $filename = uniqid('video_') . '.' . $uploadedFile->getClientOriginalExtension();
            $relativeInputPath = 'tmp/' . $filename;

            // Verifica se o arquivo temporário original existe
            if (!file_exists($uploadedFile->getRealPath())) {
                throw new \Exception("Arquivo temporário original não encontrado: " . $uploadedFile->getRealPath());
            }

            // Usa o método store do UploadedFile (mais seguro)
            $storedPath = $uploadedFile->storeAs('tmp', $filename, 'local');

            $absoluteInputPath = Storage::disk('local')->path($storedPath);

            // Verifica se o arquivo foi salvo corretamente
            if (!file_exists($absoluteInputPath)) {
                throw new \Exception("Falha ao salvar arquivo temporário: {$absoluteInputPath}");
            }
            info("Arquivo salvo com sucesso: {$absoluteInputPath}");
            return [$storedPath, $absoluteInputPath];
        } catch (\Exception $e) {
            throw new \Exception("Erro ao salvar arquivo temporário: " . $e->getMessage());
        }
    }

    private function limparArquivosTemporarios(string $relativePath, string $absolutePath): void
    {
        $cleaned = false;

        if (file_exists($absolutePath)) {
            unlink($absolutePath);
            $cleaned = true;
        }
        if (Storage::disk('local')->exists($relativePath)) {
            Storage::disk('local')->delete($relativePath);
            $cleaned = true;
        }
        if ($cleaned) {
            info("Arquivos temporários limpos: {$relativePath}");
        }
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
