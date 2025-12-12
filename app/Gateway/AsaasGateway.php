<?php

declare(strict_types=1);

namespace App\Gateway;

use App\Helpers\Asaas;
use App\Modules\Pagamentos\Dto\PixDto;
use App\Modules\Pagamentos\Dto\CartaoCreditoDto;

class AsaasGateway
{
    
    public static function CriarCobrancaPix(PixDto $pix): array
    {

        $asaas = Asaas::init();
        try {
            // cria a cobrança pix na api do asaas
        $responseCreatePix =  $asaas->post('/v3/lean/payments', $pix->toArray());
            // a respota são dos dados do checkout com o id da cobrança pix
        } catch (\Throwable $e) {
            info('Erro ao gerar cobrança pix: ' . $e->getMessage());
            throw $e;
        }
        try { 
            // obtém os dados do checkout o id é para buscar o qr code na api do asaas
            $responseCreatePixDecode = json_decode($responseCreatePix, true);
            $responseQrCodeNumeroCopiaCola = $asaas->get('v3/payments/' . $responseCreatePixDecode['id'] . '/pixQrCode');
        } catch (\Throwable $e) {
            info('Erro ao obter código pix: ' . $e->getMessage());
            throw $e;
        }

        return [$responseQrCodeNumeroCopiaCola , $responseCreatePixDecode];
    }

    // public static function CriarCobrancaCartaoCredito(CartaoCreditoDto $cartao)
    // {

    // }

}