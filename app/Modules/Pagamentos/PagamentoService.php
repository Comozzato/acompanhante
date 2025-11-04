<?php

declare(strict_types=1);

namespace App\Modules\Pagamentos;

use App\Helpers\Api;
use App\Helpers\ApiAsaas;
use Exception;

class PagamentoService
{
    private Api $api;

    public function __construct()
    {
        $uri = env('URL_SANDBOX_ASAAS');
        $token = env('ACCESS_TOKEN');
        if (is_null($uri)) {
            throw new Exception('url do serviço de pagamento asaas está vazio', 15);
        }
        if (is_null($token)) {
            throw new Exception('token de accesso vazio', 16);
        }
        $this->api = new Api($uri, [
            'access_token' => $token
        ]);
    }


    public function costumers()
    {
        $response = Api::get('v3/customers');

        return $response;
    }


    public function cadastroCostumers(array $data = [])
    {
        $response = Api::post('/v3/customers', $data);
        return $response;
    }
}
