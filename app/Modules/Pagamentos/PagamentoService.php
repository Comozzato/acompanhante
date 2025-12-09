<?php

declare(strict_types=1);

namespace App\Modules\Pagamentos;

use App\Events\CheckoutEvent;


class PagamentoService
{
    public function __construct() {}

    public function gerarCobranca(TipoPagamento $tipo_pagamento, array $data)
    {
        // metodo que gera cobranca conforme o tipo de pagamento
        $corpoDaCobranca = $tipo_pagamento->metodoDePagamento();

        // chama o metodo gerarCobranca do metodo de pagamento
        // resposta varia dependendo do metodo, é um array com os dados da cobranca
        $responseCheckout = $corpoDaCobranca->gerarCobranca($data);
        // o indice [1] é o payload da cobranca para o evento
        // o indice [0] é o dado retornado para o cliente (qr code e o pixCopiaCola)
        // evento de checkout
        CheckoutEvent::dispatch([$responseCheckout[1], $data]);
        //
        return $responseCheckout[0]; // dados do qr code ou link de pagamento
    }
}
