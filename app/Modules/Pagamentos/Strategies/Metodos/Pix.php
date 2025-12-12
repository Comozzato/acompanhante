<?php

declare(strict_types=1);

namespace App\Modules\Pagamentos\Strategies\Metodos;

use App\Gateway\AsaasGateway;
use App\Helpers\Asaas;
use App\Models\Produto;
use App\Models\User;
use App\Modules\Pagamentos\Dto\PixDto;
use App\Modules\Pagamentos\Strategies\Interfaces\Pagamentopix;
use Carbon\Carbon;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Money;
use Money\Parser\DecimalMoneyParser;

class Pix implements Pagamentopix
{   

    // funcao para gerar cobrança pix
    // escrito pelo comozzato em 05/11/2025
    // atualizado em 07/11/2025
    public function gerarCobranca(array $data)
    {    
        // busca os dados do usuário e do produto
        $user =  User::find($data['user_id']);
        if (! $user) {
            throw new \RuntimeException('Usuário não encontrado');
        }        
        $produto = Produto::find($data['produto_id']);
        // monta o payload para criar a cobrança pix na api do asaas
        if (! $produto) {
            throw new \RuntimeException('Produto não encontrado');
        }

        
        [$responseQrCodeNumeroCopiaCola , $responseCreatePixDecode ] = AsaasGateway::CriarCobrancaPix(new PixDto(
            user: $user,
            produto: $produto
        ));
        
        return [$responseQrCodeNumeroCopiaCola , $responseCreatePixDecode ];
    }
}
