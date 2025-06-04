<?php

declare(strict_types=1);

namespace App\Modules\Watermark\Services\Strategies;

enum TypeImg: string
{
    case Imagem_master = 'Imagem_master';
    case Thumbnail_prymary = 'Thumbnail_prymary';
    case Thumbnail_secondary = 'Thumbnail_secondary';
}
