<?php
namespace App\Modules\Watermark\Services;

use App\Modules\Watermark\Services\Strategies\TypeMediaValueEnum;
use App\Modules\Watermark\Services\Strategies\ImageWatermark;
use App\Modules\Watermark\Services\Strategies\VideoWatermark;
use App\Modules\Watermark\Contracts\WatermarkServiceInterface;

class WatermarkStrategy
{
    public function resolve(TypeMediaValueEnum $mediaType): WatermarkServiceInterface
    {
        return match ($mediaType) {
            TypeMediaValueEnum::IMAGE => new ImageWatermark(),
            TypeMediaValueEnum::VIDEO => new VideoWatermark(),
            default => throw new \InvalidArgumentException('Tipo de mídia não suportado para marca d\'água'),
        };
    }
}
