<?php

declare(strict_types=1);

namespace App\Modules\Clientes;

use App\Helpers\Api;
use App\Models\User;
use App\Modules\Shared\ValueObjects\IdValueObject;
use Exception;

class ClientesServices
{

    public function __construct(private Api $asaasApi) {}

    public function clientes()
    {
        $response = $this->asaasApi::get('v3/customers');

        return $response;
    }


    public function cadastroCliente(IdValueObject $userID)
    {   
        $userData = User::find($userID->getValue());
        $data = [
            'name' => $userData->name,
            'email' => $userData->email,
            //'phone' => $userData->phone,
            //'mobilePhone' => $userData->phone,
            //'cpfCnpj' => $userData->cpf_cnpj,
            //'externalReference' => $userData->id,
            'notificationDisabled' => true,
        ];
        $response = $this->asaasApi::post('/v3/customers', $data);
        return $response;
    }
}
