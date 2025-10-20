<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnuncioMidias extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

    public function toArray(Request $request): array
    {
        $post = $this->posts_info;
        return [
            'id' => $this->id,
            'nome' => $post?->nome,
            'cidade' => $post?->cidade,
            'midia' => $this->whenLoaded('midia'),
            'publicado_em' => $this->publicado_em,
        ];
    }
}
