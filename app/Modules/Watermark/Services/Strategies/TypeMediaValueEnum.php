<?php

declare(strict_types=1);

namespace App\Modules\Watermark\Services\Strategies;

enum TypeMediaValueEnum: string
{
    case IMAGE = 'image';
    case VIDEO = 'video';
}
