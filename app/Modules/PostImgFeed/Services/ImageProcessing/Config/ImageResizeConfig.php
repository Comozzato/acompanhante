<?php

namespace App\Modules\PostImgFeed\Services\ImageProcessing\Config;

class ImageResizeConfig
{
    public function __construct(
        public int $width,
        public int $height,
        public bool $cut = false
    ) {}
}
