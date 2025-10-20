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
        // Cada feed será mapeado separadamente
        $dados = collect($this->feeds)
            ->map(function ($feed) {    
                return [
                    'nome' => $this->nome,
                    'cidade' => $this->cidade,
                    'feed' => [
                        'id' => $feed->id,
                        'descricao' => $feed->post,
                        'midias' => $feed->midia,
                    ],
                ];
            })
            ->filter() // remove vazios
            ->values()
            ->toArray();

        return $dados;
    }
}
