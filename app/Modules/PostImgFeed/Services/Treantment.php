<?php

declare(strict_types=1);

namespace App\Modules\PostImgFeed\Services;



use App\Enums\ImagemFeed;
use App\Modules\PostImgFeed\Services\WatermarkStrategy;


class Treantment
{

    public function __construct(private WatermarkStrategy $watermarkStrategy) {}

    public function processImageFeed($file): array
    {
        if (!$file) {
            throw new \InvalidArgumentException('Arquivo não fornecido');
        }
        $imagensFeed = [
            ImagemFeed::NO_WATERMARK,
            ImagemFeed::MASTER,
            ImagemFeed::THBPRIMARY,
            ImagemFeed::THBSECUNDARY,
        ];
        $path = [];
        // Verifica se o arquivo é uma imagem ou vídeo
        if (in_array($file->getClientOriginalExtension(), ['jpeg', 'png'])) {
            // Processa cada tipo de imagem com a estratégia de marca d'água
            foreach ($imagensFeed as $img) {
                $path[] = $this->watermarkStrategy->resolve($img, $file);
            }
        }
        if (in_array($file->getClientOriginalExtension(), ['mp4', 'mov', 'avi', 'mkv'])) {
            // Se for um vídeo, adiciona o caminho do vídeo processado
            $paths = $this->watermarkStrategy->resolve(ImagemFeed::VIDEO, $file);
            $path = json_decode($paths, true);
        }
        return $path;
    }
}
