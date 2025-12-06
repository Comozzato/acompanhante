<?php

declare(strict_types=1);

namespace App\Modules\PostImgFeed\Services\ImageProcessing\Config;

class PositionConfig
{
    public PositionXConfig $x;
    public PositionYConfig $y;

    public function __construct(PositionXConfig $x, PositionYConfig $y)
    {
        $this->x = $x;
        $this->y = $y;
    }
}
