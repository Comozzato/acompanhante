<?php

declare(strict_types=1);

namespace App\Modules\Pagamentos\Dto\CartaoObjetos;

class CreditCardHolderDto
{
    public function __construct(
        public string $name,
        public string $email,
        public string $cpfCnpj,
        public string $postalCode,
        public string $addressNumber,
        public ?string $addressComplement = null,
        public ?string $phone = null,
        public ?string $mobilePhone = null,
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'cpfCnpj' => $this->cpfCnpj,
            'postalCode' => $this->postalCode,
            'addressNumber' => $this->addressNumber,
            'addressComplement' => $this->addressComplement,
            'phone' => $this->phone,
            'mobilePhone' => $this->mobilePhone,
        ];
    }
}