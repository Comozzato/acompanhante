<?php

declare(strict_types=1);

namespace App\Modules\PostImgFeed\Services\Strategies;
enum TypeImg: string
{
    case Imagem_master = 'Imagem_master';
    case Thumbnail_primary = 'Thumbnail_primary';
    case Thumbnail_secondary = 'Thumbnail_secondary';
}
