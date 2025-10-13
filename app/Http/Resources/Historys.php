<?php

namespace App\Http\Resources;

use App\Services\S3ImageGalleryService;
use Exception;
use FFMpeg\FFProbe;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;
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
                    return ['url' => $url, 'duration' => (int) $metadata['duration']];
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
            'timeout' => 3600,
            'ffmpeg.threads' => 12,
        ]);


        return Cache::rememberForever("video_meta_{$videoId}", function () use ($path, $ffprobe) {
            try {
                // 1️⃣ Tenta usar URL temporária (mais rápido)
                $url = Storage::disk('s3')->temporaryUrl($path, now()->addMinutes(5));

                $format = $ffprobe->format($url);
                $stream = $ffprobe->streams($url)->videos()->first();
            } catch (Exception $e) {
                // 2️⃣ Se falhar (ex: servidor não suporta byte-range), baixa localmente
                $tempDir = storage_path('app/private/temp');
                if (!file_exists($tempDir)) mkdir($tempDir, 0755, true);

                $temp = $tempDir . '/' . basename($path);

                $streamFile = Storage::disk('s3')->readStream($path);
                file_put_contents($temp, stream_get_contents($streamFile));
                fclose($streamFile);

                $format = $ffprobe->format($temp);
                $stream = $ffprobe->streams($temp)->videos()->first();

                unlink($temp); // limpa o arquivo local temporário
            }

            return [
                'duration' => $format->get('duration'),
                'bitrate' => $format->get('bit_rate'),
                'codec' => $stream?->get('codec_name'),
                'width' => $stream?->get('width'),
                'height' => $stream?->get('height'),
                'size' => $format->get('size'),
            ];
        });
    }
}
