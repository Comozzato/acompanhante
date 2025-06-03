<?php

declare(strict_types=1);

namespace App\Modules\PostImgFeed\Services;

use App\Modules\Watermark\Services\Strategies\TypeImg;
use Illuminate\Http\File;

class Treantment
{
    public function ImageFeed(?File $file)
    {   
        if (!$file) {
            throw new \InvalidArgumentException('Arquivo não fornecido');
        }
        $waterMarksStrategy = new WatermarkStrategy();
        $outputFileMaster =  $waterMarksStrategy->resolve(TypeImg::Imagem_master)
            ->applyWatermark($file);

        return $outputFileMaster;
    }
}