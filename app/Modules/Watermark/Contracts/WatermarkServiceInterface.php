<?php
namespace App\Modules\Watermark\Contracts;

interface WatermarkServiceInterface
{
    /**
     * Aplica marca d'água em um arquivo e retorna o caminho do novo arquivo.
     * @param string $inputFile Caminho do arquivo original
     * @param string $watermark Caminho ou texto da marca d'água
     * @param string|null $outputFile Caminho do arquivo de saída (opcional)
     * @return string Caminho do arquivo com marca d'água
     */
    public function applyWatermark(string $inputFile, string $watermark, ?string $outputFile = null): string;
}
