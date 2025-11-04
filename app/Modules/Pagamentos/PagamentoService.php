<?php

declare(strict_types=1);

namespace App\Modules\Pagamentos;

use App\Helpers\Api;
use App\Helpers\ApiAsaas;
use App\Modules\Pagamentos\Strategies\Pagamento;
use Exception;

class PagamentoService
{
    public function __construct() {}

    public function gerarCobranca(TipoPagamento $tipo_pagamento, array $data)
    {

        $corpoDaCobranca = $tipo_pagamento->metodoDePagamento();

        return $corpoDaCobranca->gerarCobranca($data);
    }
}
