<?php
declare(strict_types=1);

namespace App\Modules\PostImgFeed\Services;

use App\Enums\ImagesFeed;
use App\Modules\PostImgFeed\Contracts\ImageWatermark;
use App\Modules\PostImgFeed\Services\Strategies\MasterImageWaterMark;
use InvalidArgumentException;

class WatermarkStrategy
{
    /**
     * O mapa agora contém as INSTÂNCIAS das estratégias.
     * @var array<string, ImageWatermark>
     */
    private array $strategyMap = [];

    /**
     * O construtor recebe as instâncias e as organiza no mapa.
     * @param iterable<ImageWatermark> $strategies
     */
    public function __construct(iterable $strategies)
    {
        foreach ($strategies as $strategy) {
            $this->strategyMap[$strategy->getSupportedType()->value] = $strategy;
        }
    }

    public function resolve(ImagesFeed $value, $file): string
    {

        $key = $value->value;

        // 2. Verifique se a chave existe no mapa de instâncias
        if (!isset($this->strategyMap[$key])) {
            throw new \InvalidArgumentException("Não há uma estratégia de marca d'água registrada para o tipo '{$key}'.");
        }

        return $this->strategyMap[$key]->applyWatermark($file);
    }
}
