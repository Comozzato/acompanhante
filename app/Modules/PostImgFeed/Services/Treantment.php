<?php

declare(strict_types=1);

namespace App\Modules\PostImgFeed\Services;


use App\Enums\ImagesFeed;
use App\Modules\PostImgFeed\Services\Strategies\TypeImg;
use App\Modules\PostImgFeed\Services\WatermarkStrategy;


class Treantment
{

    public function __construct(private WatermarkStrategy $watermarkStrategy)
    {
    }

    public function processImageFeed($file)
    {
         if (!$file) {
             throw new \InvalidArgumentException('Arquivo não fornecido');
        }
        $outputMaster =  $this->watermarkStrategy->resolve(ImagesFeed::MASTER, $file);

        return $outputMaster;
    }
}
