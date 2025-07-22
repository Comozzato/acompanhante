<?php
namespace App\Modules\PostImgFeed\Contracts;
use App\Enums\ImagemFeed;

interface ImageWatermark
{
    public function applyWatermark($inputFile): string;
    public function getSupportedType(): ImagemFeed;

}
