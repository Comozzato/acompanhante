<?php

declare(strict_types=1);

namespace App\Modules\Pagamentos\Strategies\Metodos;

use App\Helpers\Api;
use App\Modules\Pagamentos\Strategies\Pagamento;

class Pix implements Pagamento
{
    private string $pixKey;

    public function __construct()
    {
        $this->pixKey = env('CHAVE_PIX');
    }
    public function gerarCobranca() {}
}
