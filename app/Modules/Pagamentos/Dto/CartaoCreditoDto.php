<?php

declare(strict_types=1);

namespace App\Modules\Pagamentos\Dto;

use App\Modules\Pagamentos\Dto\CartaoObjetos\CreditCardDto;
use App\Modules\Pagamentos\Dto\CartaoObjetos\CreditCardHolderDto;
use Money\Money;

class PagamentoCartaoDto
{
    public function __construct(
        public string $customerId,
        public Money $valor,
        public string $dueDate,
        public int $installmentCount,
        public CreditCardDto $creditCard,
        public CreditCardHolderDto $holder,
        public ?string $remoteIp = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'customer' => $this->customerId,
            'billingType' => 'CREDIT_CARD',
            'value' => $this->valor->getAmount(),
            'dueDate' => $this->dueDate,
            'installmentCount' => $this->installmentCount > 1 ? $this->installmentCount : null,
            'installmentValue' => 0, // regra fixa

            'creditCard' => $this->creditCard->toArray(),
            'creditCardHolderInfo' => $this->holder->toArray(),

            'remoteIp' => $this->remoteIp ?? request()->ip(),
        ]);
    }
}