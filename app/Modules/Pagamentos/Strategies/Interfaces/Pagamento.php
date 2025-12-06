<?php

declare(strict_types=1);

namespace App\Modules\Pagamentos\Strategies\Interfaces;

interface Pagamento
{
    public function gerarCobranca(array $data);
}

