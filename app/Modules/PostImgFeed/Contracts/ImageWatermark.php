<?php
namespace App\Modules\PostImgFeed\Contracts;
interface ImageWatermark
{
    public function applyWatermark(string $inputFile, ?string $outputFile = null): string;
}
