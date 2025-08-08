<?php

declare(strict_types=1);

namespace App\Modules\PostImgFeed\Services\ImageProcessing\Config;

class PositionXConfig
{
    private string|int $position;
    private int $padding;

    /**
     * @param string|int $position - 'left' | 'center' | 'right' | ou valor numérico customizado
     */
    public function __construct(string|int $position = 'center', int $padding = 10)
    {
        $this->position = $position;
        $this->padding = $padding;
    }

    /**
     * Calcula o valor de X com base na posição padrão ou valor absoluto.
     */
    public function resolveX(int $imageWidth, int $watermarkWidth): int
    {
        // Se for número, retorna diretamente (valor fixo)
        if (is_int($this->position)) {
            return max(0, $this->position); // evita negativos
        }

        return match (strtolower($this->position)) {
            'left'   => $this->padding,
            'center' => intval(($imageWidth - $watermarkWidth) / 2),
            'right'  => $imageWidth - $watermarkWidth - $this->padding,
            default  => intval(($imageWidth - $watermarkWidth) / 2), // fallback
        };
    }
}
