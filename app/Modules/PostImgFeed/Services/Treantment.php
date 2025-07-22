<?php

declare(strict_types=1);

namespace App\Modules\PostImgFeed\Services;



use App\Enums\ImagemFeed;
use App\Modules\PostImgFeed\Services\WatermarkStrategy;


class Treantment
{

    public function __construct(private WatermarkStrategy $watermarkStrategy)
    {
    }

    public function processImageFeed($file): array
    {
         if (!$file) {
             throw new \InvalidArgumentException('Arquivo não fornecido');
        }
        $imagensFeed = [
            ImagemFeed::MASTER,
            ImagemFeed::THBPRIMARY,
            ImagemFeed::THBSECUNDARY,
        ];
        $path = [];
        foreach($imagensFeed as $img)
        {
           $path[] = $this->watermarkStrategy->resolve($img, $file);
        }

        return $path;
    }
}
