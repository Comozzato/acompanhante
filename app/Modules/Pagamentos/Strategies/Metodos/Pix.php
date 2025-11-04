<?php

declare(strict_types=1);

namespace App\Modules\Pagamentos\Strategies\Metodos;

use App\Helpers\AsaasApi;
use App\Modules\Pagamentos\Strategies\Interfaces\Pix as InterfacesPix;
use App\Modules\Pagamentos\TipoPagamento;
use Carbon\Carbon;

class Pix implements InterfacesPix
{
    private string $pixKey;

    public function __construct()
    {
        $this->pixKey = env('CHAVE_PIX');
    }
    public function gerarCobranca(array $data)
    {
        $payload = [
            'customer' => $data['customer'],
            "description" => $data['description'],
            "value" => $data['value'],
            "dueDate" => $data['dueDate'],
            "billingType" => 'PIX'
        ];

        $responseCreatePix =  json_decode(AsaasApi::api()->post('/v3/lean/payments', $payload), true);
        $responseQrCodeNumeroCopiaCola = AsaasApi::api()->get('v3/payments/' . $responseCreatePix['id'] . '/pixQrCode');
        return $responseQrCodeNumeroCopiaCola;
    }
}
