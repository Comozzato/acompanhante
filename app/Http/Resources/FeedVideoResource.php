<?php

namespace App\Http\Resources;

use App\Services\S3ImageGalleryService;
use Illuminate\Http\Resources\Json\JsonResource;

class FeedVideoResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'          => $this->id,
            'post_id'     => $this->post_id,
            'publicado_em' => $this->publicado_em,
            'midia' => $this->formatMidias(),
        ];
    }

    private function formatMidias()
    {
        // Agrupar vídeos e thumbnails do mesmo feed
        $videos = $this->midia->filter(fn($m) => $this->isVideo($m->midia))->values();
        $thumbs = $this->midia->filter(fn($m) => $this->isThumb($m->midia))->values();

        $result = [];

        foreach ($videos as $i => $video) {
            $result[] = [
                'thumb' => S3ImageGalleryService::getImage($thumbs[$i]->midia) ?? null, // pega a thumb correspondente se existir
                'file'  => S3ImageGalleryService::getImage($video->midia),
            ];
        }

        return $result;
    }

    private function isVideo($filename)
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($ext, ['mp4', 'avi', 'mov']);
    }

    private function isThumb($filename)
    {
        return str_contains($filename, 'thumb'); // ajuste conforme seu padrão
    }
}
