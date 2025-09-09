<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class Anuncios extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Aqui pegamos o retorno padrão do recurso
        $data = parent::toArray($request);

        // Removemos as imagens
        return Arr::except($data, ['imgcapa', 'imgevidencias', 'imgatualizadas']);
    }
}
