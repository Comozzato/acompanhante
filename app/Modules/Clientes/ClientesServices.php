<?php

declare(strict_types=1);

namespace App\Modules\Clientes;

use App\Helpers\Api;
use Exception;

class ClientesServices
{

    public function __construct(private Api $asaasApi) {}

    public function clientes()
    {
        $response = $this->asaasApi::get('v3/customers');

        return $response;
    }


    public function cadastroCliente(array $data = [])
    {
        $response = $this->asaasApi::post('/v3/customers', $data);
        
        return $response;
    }
}
