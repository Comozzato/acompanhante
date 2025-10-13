<?php

namespace App\Http\Resources;

use App\Services\S3ImageGalleryService;
use FFMpeg\FFProbe;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
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
                $Video = $this->isVideo($file, true);
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


    private function isVideo($filename, $getFileName = false)
    {
        foreach ($filename as $file) {
            $filename = $file['midia'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (in_array($ext, ['mp4', 'avi', 'mov'])) {
                if ($getFileName) {
                    Storage::disk('s3')->setVisibility($filename, 'public');
                    $url = S3ImageGalleryService::getImage($filename);

                    $metadata = $this->getVideoMetadata($filename);
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

    private function getVideoMetadata($path)
    {
        $ffprobe = FFProbe::create([
            'ffprobe.binaries' => env('FFPROBE_BINARIES'),
            'timeout' => 3600,
            'ffmpeg.threads' => 12,
        ]);

        // Baixa temporariamente se for S3
        if (Storage::disk('s3')->exists($path)) {
            $temp = tempnam(sys_get_temp_dir(), 'video_');
            file_put_contents($temp, Storage::disk('s3')->get($path));
            $path = $temp;
        } else {
            $path = storage_path('app/' . $path);
        }

        $format = $ffprobe->format($path);
        $stream = $ffprobe->streams($path)->videos()->first();

        return [
            'duration' => (float) $format->get('duration'),
            'bitrate' => (int) $format->get('bit_rate'),
            'codec' => $stream?->get('codec_name'),
            'width' => $stream?->get('width'),
            'height' => $stream?->get('height'),
            'fps' => $stream?->get('avg_frame_rate'),
            'rotation' => $stream?->get('tags')['rotate'] ?? 0,
            'format_name' => $format->get('format_name'),
        ];
    }
}
