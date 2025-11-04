<?php

declare(strict_types=1);

namespace App\Modules\Clientes;

use App\Helpers\Api;
use App\Helpers\AsaasApi;
use Exception;

class ClientesServices
{

    public function __construct(private AsaasApi $api) {}

    public function clientes()
    {
        $response = AsaasApi::Assas()->get('v3/customers');

        return $response;
    }


    public function cadastroCliente(array $data = [])
    {
        $response = AsaasApi::Assas()->post('/v3/customers', $data);
        return $response;
    }
}
