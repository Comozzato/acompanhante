<?php

declare(strict_types=1);

namespace App\Modules\Pagamentos\Dto;

use App\Models\Produto;
use App\Models\User;
use Carbon\Carbon;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Money;

class PixDto
{
    public function __construct(
        public User $user,
        public Produto $produto
    ) 
    {
    }

    public function toArray(): array
    {
        return [
            'customer' => $this->user->asaas_customer_id,
            'description' => $this->produto->descricao,
            'value' => $this->formatValue(),
            'billingType' => 'PIX',
            'dueDate'         => Carbon::now()->format('Y-m-d'),
            'expirationDate'  => Carbon::now()->addMinutes(30)->toIso8601String(),
        ];
    }

    private function formatValue(): string
    {   
        $formatter = new DecimalMoneyFormatter(new ISOCurrencies());
        $preco =  $formatter->format(Money::BRL($this->produto->preco));
        return $preco;
    }   
}