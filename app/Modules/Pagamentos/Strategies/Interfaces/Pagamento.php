<?php

declare(strict_types=1);

namespace App\Modules\Pagamentos\Strategies;

interface Pagamento
{
    public function gerarCobranca();
}

