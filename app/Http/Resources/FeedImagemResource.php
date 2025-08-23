<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeedImagemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'post_id'     => $this->post_id,
            'publicado_em' => $this->publicado_em,
            'midia' => $this->formatImages($this->midia->toArray()), // Converte a coleção para array
        ];
    }

    private function formatImages(array $images): array
    {
        $formatted = [];
        foreach ($images as $image) {
            $type = $this->getImageType($image['midia']);
            $formatted[$type][] = $image['url'];
        }

        return $formatted;
    }

    private function getImageType($filename)
    {
        if (str_contains($filename, 'master')) {
            return 'file';
        }
        if (str_contains($filename, 'primary')) {
            return 'primary';
        }
        if (str_contains($filename, 'secundary')) {
            return 'thumb';
        }
    }
}
