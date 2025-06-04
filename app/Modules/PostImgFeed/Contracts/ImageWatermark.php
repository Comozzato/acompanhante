<?php
namespace App\Modules\Watermark\Contracts;

interface ImageWatermark
{
    public function applyWatermark(string $inputFile, ?string $outputFile = null): string;
}
