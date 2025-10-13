<?php

namespace App\Http\Resources;

use App\Services\S3ImageGalleryService;
use Exception;
use FFMpeg\FFProbe;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class Historys extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $imageEvidencias = $this->imgevidencias;

        $items = $this->formatStories();

        // se não tiver items, retorna array vazio
        if (empty($items)) {
            return [];
        }
        return  [
            "authorImage" => $imageEvidencias,
            "coverImage" => $imageEvidencias,
            "coverAuthorImage" => $imageEvidencias,
            "authorName" => $this->nome,
            "coverName" => $this->nome,
            "commonName" => "",
            "link" => "javascript:void(0);",
            'items' => $items,
            'type' => 'image',
        ];
    }

    private function formatStories()
    {
        $stories = [];
        $button = json_decode('{
            "link": "' . ($this->url ?? 'javascript:void(0);') . '",
            "linkText": "' . $this->nome . '",
            "target": "_self"
        }');


        foreach ($this->feeds as $feed) {

            $file = $feed->midia;
            if ($this->isVideo($file)) {
                $Video = $this->isVideo($file, true, $feed->id);
                $duration = $Video['duration'] < 30 ? $Video['duration'] : 30;
                $stories[] = [
                    'type' => 'video',
                    "length" => $duration,
                    'src'  => $Video['url'],
                    'publicado_em' => $feed->publicado_em,
                    'button' => $button
                ];
            } else {

                $stories[] = [
                    'type' => 'image',
                    'length' => 3,
                    'src'  => $this->formatImages($file->toArray()),
                    'publicado_em' => $feed->publicado_em,
                    'button' => $button
                ];
            }
        }
        usort($stories, fn($a, $b) => strtotime($b['publicado_em']) <=> strtotime($a['publicado_em']));
        return $stories;
    }


    private function isVideo($filename, $getFileName = false, $videoId = null)
    {
        foreach ($filename as $file) {
            $filename = $file['midia'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (in_array($ext, ['mp4', 'avi', 'mov'])) {
                if ($getFileName) {
                    Storage::disk('s3')->setVisibility($filename, 'public');
                    $url = S3ImageGalleryService::getImage($filename);

                    $metadata = $this->getVideoMetadata($filename, $videoId);
                    return ['url' => $url, 'duration' => $metadata['duration']];
                }
                return true;
            }
        }

        return false;
    }

    private function getImageType($filename)
    {
        if (str_contains($filename['midia'], 'primary')) {
            return 'primary';
        }
        return null; // Retorna null para tipos não especificados
    }

    private function formatImages(array $images): array
    {

        //dd($images);
        $formatted = [];
        foreach ($images as $image) {
            $type = $this->getImageType($image);
            if ($type == null) {
                continue;
            }
            $formatted[] = $image['url'];
        }

        return $formatted;
    }

    function getVideoMetadata($path, $videoId)
    {
        $ffprobe = FFProbe::create([
            'ffprobe.binaries' => env('FFPROBE_BINARIES'),
            'timeout'          => 3600,
            'ffmpeg.threads'   => 12,
        ]);

        // A chave do cache continua a mesma
        $cacheKey = "video_meta_{$videoId}";

        return Cache::rememberForever($cacheKey, function () use ($path, $ffprobe) {

            // 1️⃣ Tenta acessar diretamente com URL gerada pelo Laravel
            // Isso funciona para arquivos PÚBLICOS.
            // Se seus arquivos forem privados, use:
            // $url = Storage::disk('s3')->temporaryUrl($path, now()->addMinutes(5));
            $url = Storage::disk('s3')->url($path);

            try {
                Log::info("Tentando obter metadados via URL para o caminho: {$path}");
                $format = $ffprobe->format($url);
                $stream = $ffprobe->streams($url)->videos()->first();
                Log::info("Metadados obtidos com sucesso via URL.");
            } catch (Exception $e) {
                // Logar o erro original é crucial para o diagnóstico!
                Log::warning("Falha ao obter metadados via URL. Iniciando fallback de download local.", [
                    'path' => $path,
                    'error' => $e->getMessage(),
                ]);

                // 2️⃣ Se falhar, baixa localmente de forma otimizada
                // Usando a barra normal, que é compatível com todos os sistemas
                $tempDir = storage_path('app/private/temp');
                if (!file_exists($tempDir)) {
                    mkdir($tempDir, 0755, true);
                }

                // Use DIRECTORY_SEPARATOR para compatibilidade entre Windows e Linux
                $tempPath = $tempDir . DIRECTORY_SEPARATOR . basename($path);

                // Otimização: Faz o streaming do S3 direto para um arquivo local sem sobrecarregar a memória
                try {
                    $readStream = Storage::disk('s3')->readStream($path);
                    $writeStream = fopen($tempPath, 'w+b');
                    stream_copy_to_stream($readStream, $writeStream);
                    fclose($readStream);
                    fclose($writeStream);
                } catch (Exception $downloadException) {
                    // Se o download falhar, limpa e lança uma exceção
                    if (file_exists($tempPath)) {
                        unlink($tempPath);
                    }
                    throw new Exception("Falha ao baixar o arquivo do S3 para análise: " . $downloadException->getMessage());
                }

                $format = $ffprobe->format($tempPath);
                $stream = $ffprobe->streams($tempPath)->videos()->first();
                unlink($tempPath); // Limpa o arquivo local temporário
            }

            return [
                'duration' => $format->has('duration') ? (int) $format->get('duration') : 0,
                'bitrate'  => $format->has('bit_rate') ? $format->get('bit_rate') : 0,
                'codec'    => $stream?->get('codec_name'),
                'width'    => $stream?->get('width'),
                'height'   => $stream?->get('height'),
                'size'     => $format->has('size') ? $format->get('size') : 0,
            ];
        });
    }
}
