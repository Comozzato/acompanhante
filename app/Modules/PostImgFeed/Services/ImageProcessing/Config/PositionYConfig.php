<?php

declare(strict_types=1);

namespace App\Modules\PostImgFeed\Services\ImageProcessing\Config;

class PositionYConfig
{
    private string|int $position;
    private int $padding;

    /**
     * @param string|int $position - 'top' | 'middle' | 'bottom' | ou valor numérico customizado
     */
    public function __construct(string|int $position = 'middle', int $padding = 10)
    {
        $this->position = $position;
        $this->padding = $padding;
    }

    /**
     * Calcula o valor de Y com base na posição padrão ou valor absoluto.
     */
    public function resolveY(int $imageHeight, int $watermarkHeight): int
    {
        // Se for número, retorna diretamente (valor fixo)
        if (is_int($this->position)) {
            return max(0, $this->position); // evita negativos
        }

        return match (strtolower($this->position)) {
            'top' => $this->padding,
            'middle' => intval(($imageHeight - $watermarkHeight) / 2),
            'bottom' => $imageHeight - $watermarkHeight - $this->padding,
            default => intval(($imageHeight - $watermarkHeight) / 2), // fallback
        };
    }

    public function getRaw(): string|int
    {
        return $this->position;
    }
}
