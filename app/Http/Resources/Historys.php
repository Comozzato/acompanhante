<?php

namespace App\Http\Resources;

use App\Services\S3ImageGalleryService;
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
            'url' => $this->url,
        ];
    }

    private function formatStories()
    {
        $stories = [];
        $button = json_decode('{
            "link": "javascript:false(0);",
            "linkText": "' . $this->nome . '",
            "target": "_self"
        }');


        foreach ($this->feeds as $feed) {

            $file = $feed->midia;
            if ($this->isVideo($file)) {
                $stories[] = [
                    'type' => 'video',
                    "length" => 14,
                    'src'  => $this->isVideo($file, true),
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
            if ($getFileName) {
                Storage::disk('s3')->setVisibility($filename, 'public');
                return S3ImageGalleryService::getImage($filename);
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
}
