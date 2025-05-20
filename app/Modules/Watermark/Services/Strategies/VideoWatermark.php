<?php
namespace App\Modules\Watermark\Services\Strategies;

use App\Modules\Watermark\Contracts\WatermarkServiceInterface;

class VideoWatermark implements WatermarkServiceInterface
{
    public function applyWatermark(string $inputFile, string $watermark, ?string $outputFile = null): string
    {
        // Implementação futura para aplicar marca d'água em vídeo
        // Exemplo: usar FFmpeg
        return '';
    }
}
