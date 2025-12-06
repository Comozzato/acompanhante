<?php
namespace App\Modules\PostImgFeed\Contracts;
use App\Enums\ImagemFeed;
use Illuminate\Http\UploadedFile;

interface ImageWatermark
{
    public function applyWatermark(UploadedFile $inputFile): string;
    public function getSupportedType(): ImagemFeed;

}
