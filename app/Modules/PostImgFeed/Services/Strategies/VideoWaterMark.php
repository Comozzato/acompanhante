<?php

declare(strict_types=1);

namespace App\Modules\PostImgFeed\Services\Strategies;

use App\Enums\ImagemFeed;
use App\Modules\PostImgFeed\Contracts\ImageWatermark;
use FFMpeg\FFProbe;
use FFMpeg\Format\Video\X264;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use FFMpeg\FFMpeg;  // <- esta é a classe da lib PHP-FFMpeg pura
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg as LaravelFFMpeg;
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
        // 1️⃣ Salva upload temporário no storage local
        [$relativeInputPath, $absoluteInputPath] = $this->salvarUploadTemporario($uploadedFile);

        // 2️⃣ Define caminho de saída no S3
        $relativeOutputPath = auth_user()->id . '/posts/video-' . uniqid() . '.mp4';

        // 3️⃣ Garante que o vídeo temporário existe no disco local
        if (!file_exists($absoluteInputPath)) {
            throw new \RuntimeException("Arquivo não foi movido corretamente: $absoluteInputPath");
        }

        // 5️⃣ Processa o vídeo e envia para o S3
        $paths = $this->criarVideoAplicarWaterMark($relativeInputPath, $relativeOutputPath);

        // 6️⃣ Valida o arquivo processado no S3
        if (!Storage::disk('s3')->exists($relativeOutputPath)) {
            throw new \RuntimeException("Arquivo processado não encontrado no S3: {$relativeOutputPath}");
        }
        $fileSize = Storage::disk('s3')->size($relativeOutputPath);
        if ($fileSize <= 0) {
            throw new \RuntimeException("Arquivo processado está vazio ou corrompido no S3: {$relativeOutputPath}");
        }
        // 7️⃣ Limpa arquivos temporários locais (com segurança)
        if (file_exists($absoluteInputPath)) {
            unlink($absoluteInputPath);
        }
        if (Storage::disk('local')->exists($relativeInputPath)) {
            Storage::disk('local')->delete($relativeInputPath);
        }

        info("Limpeza concluída: {$relativeInputPath} removido do storage local.");

        // 8️⃣ Retorna os caminhos do vídeo e thumbnail
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
        info("Iniciando processamento de vídeo: {$relativeInputPath} -> {$relativeOutputPath}");

        info('Criando instancia do FFMpeg');
        // Cria instância do FFMpeg (puro)
        $ffmpeg = FFMpeg::create([
            'ffmpeg.binaries'  => env('FFMPEG_BINARIES'),
            'ffprobe.binaries' => env('FFPROBE_BINARIES'),
            'timeout'          => 3600,
            'ffmpeg.threads'   => 2, // força 2 threads em todas as execuções
        ]);
        // Abre o vídeo
        info("Abrindo vídeo para processamento: {$relativeInputPath}");
        $video = LaravelFFMpeg::fromDisk('local', $ffmpeg)->open($relativeInputPath);
        info('Aplicando marca d\'água ao vídeo');
        // Aplica a marca d'água
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
        info("Exportando vídeo processado para S3: {$relativeOutputPath}");
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

        // Torna o vídeo público
        Storage::disk('s3')->setVisibility($relativeOutputPath, 'public');
        info("Vídeo exportado e salvo no S3: {$relativeOutputPath}");
        $thumbPath = $this->thumbnailFromExported($relativeOutputPath);
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

        // Usa o caminho real do disco 'local' — evita erro de /tmp/tmp/
        info('Obtendo dimensões do vídeo com FFProbe');
        // Caminho completo do arquivo no storage local
        // $fullPath = storage_path('app/private/' . $relativeInputPath); //
        $fullPath = Storage::disk('local')->path($relativeInputPath);
        info("Caminho completo do vídeo: {$fullPath}");
        if (!file_exists($fullPath)) {
            throw new \RuntimeException("Arquivo não encontrado para FFProbe: {$fullPath}");
        }

        // Cria instância do FFProbe
        info('Criando instancia do FFProbe');

        $ffprobe = FFProbe::create([
            'ffprobe.binaries' => env('FFPROBE_BINARIES'),
            'timeout'          => 3600,
            'ffmpeg.threads'   => 1,
        ]);

        // Obtém dimensões do vídeo
        info('Lendo dimensões do vídeo');
        $stream = $ffprobe->streams($fullPath)->videos()->first();
        if (!$stream) {
            throw new \RuntimeException("Não foi possível obter informações de vídeo de: {$fullPath}");
        }

        $dimensions = $stream->getDimensions();
        $vwidth = $dimensions->getWidth();
        $vheight = $dimensions->getHeight();

        // Array de larguras suportadas
        $warr = [240, 426, 480, 640, 720, 854, 1080, 1280, 1920];

        // Encontra a largura mais próxima para baixo
        if (!in_array($vwidth, $warr)) {
            $filtered = array_filter($warr, fn($w) => $w <= $vwidth);
            $vwidth = !empty($filtered) ? max($filtered) : min($warr);
        }


        $wmf_path = $camtn . $vwidth . '.png';
        $wmurl_path = $camtn . 'www' . $vwidth . '.png';
        if (!file_exists(public_path($wmf_path)) || !file_exists(public_path($wmurl_path))) {
            throw new \RuntimeException("Arquivos de marca d'água não encontrados para largura: {$vwidth}px");
        }
        info("Caminho da marca d'água: {$wmf_path}");
        info("Caminho da marca d'água (www): {$wmurl_path}");
        return [$wmf_path, $wmurl_path, $vwidth, $vheight];
    }


    private function thumbnailFromExported(string $relativeOutputPath): string
    {
        info('Gerando thumbnail do vídeo');
        // Usa LaravelFFMpeg para consistência com o vídeo
        info('Criando instancia do LaravelFFMpeg');
        info("Caminho do vídeo para thumbnail: {$relativeOutputPath}");
        if (!Storage::disk('s3')->exists($relativeOutputPath)) {
            throw new \RuntimeException("Arquivo de vídeo não encontrado no S3 para thumbnail: {$relativeOutputPath}");
        }
        if (Storage::disk('s3')->size($relativeOutputPath) <= 0) {
            throw new \RuntimeException("Arquivo de vídeo está vazio ou corrompido no S3 para thumbnail: {$relativeOutputPath}");
        }
        $processedVideo = LaravelFFMpeg::fromDisk('s3')->open($relativeOutputPath);
        $thumbnail_local_path = auth_user()->id . '/posts/video-thumbnail_' . uniqid() . '.png';
        $processedVideo->getFrameFromSeconds(1)
            ->export()
            ->toDisk('s3')
            ->save($thumbnail_local_path);
        return $thumbnail_local_path;
    }
}
