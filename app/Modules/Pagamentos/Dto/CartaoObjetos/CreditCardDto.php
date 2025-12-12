<?php

declare(strict_types=1);

namespace App\Modules\Pagamentos\Dto\CartaoObjetos;

class CreditCardDto 
{
    public function __construct(
        public string $holderName,
        public string $number,
        public string $expiryMonth,
        public string $expiryYear,
        public string $ccv,
    ) {}

    public function toArray(): array
    {
        return [
            'holderName' => $this->holderName,
            'number' => $this->number,
            'expiryMonth' => $this->expiryMonth,
            'expiryYear' => $this->expiryYear,
            'ccv' => $this->ccv,
        ];
    }
}