<?php
namespace App\Modules\PostImgFeed\Contracts;
use App\Enums\ImagesFeed;

interface ImageWatermark
{
    public function applyWatermark($inputFile): string;
    public function getSupportedType(): ImagesFeed;

}
