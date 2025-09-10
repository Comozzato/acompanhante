<?php

namespace App\Http\Resources;

use App\Services\S3ImageGalleryService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class Historys extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return  [
            "authorImage" => $this->imgevidencias,
            'coverImage' => $this->imgevidencias,
            "coverAuthorImage" => $this->imgevidencias,
            "authorName" => $this->nome,
            "coverName" => $this->nome,
            "commonName" => "",
            "coverImage" => $this->imgatualizadas,
            "link" => "javascript:void(0);",
            'items' => $this->formatStories()
        ];
    }

    private function formatStories()
    {
        $stories = [];

        foreach ($this->feeds as $feed) {

            $file = $feed->midia;
            if ($this->isVideo($file)) {
                $stories[] = [
                    'type' => 'video',
                    'src'  => S3ImageGalleryService::getImage($this->isVideo($file, true)),
                    'publicado_em' => $feed->publicado_em,
                    'button' => json_decode('{
                        "link": "",
                        "linkText": "",
                        "target": "_self"
                    }'),
                ];
            } else {
                $stories[] = [
                    'type' => 'image',
                    'length' => 14,
                    'src'  => $this->formatImages($file->toArray()),
                    'publicado_em' => $feed->publicado_em,
                    'button' => json_decode('{
                        "link": "",
                        "linkText": "",
                        "target": "_self"
                    }'),
                ];
            }
        }
        usort($stories, fn($a, $b) => strtotime($b['publicado_em']) <=> strtotime($a['publicado_em']));
        return $stories;
    }

    private function formatMidias($files) {}
    private function isVideo($filename, $getFileName = false)
    {
        foreach ($filename as $file) {
            $filename = $file['midia'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            if ($getFileName) {
                return $filename;
            }
            return in_array($ext, ['mp4', 'avi', 'mov']);
        }
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
}
