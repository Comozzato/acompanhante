<?php

declare(strict_types=1);

namespace App\Modules\Clientes;

use App\Helpers\Api;
use App\Http\Controllers\Controller;
use App\Modules\Shared\ValueObjects\IdValueObject;
use Illuminate\Http\Request;

class ClientesController extends Controller
{
    public function __construct(private ClientesServices $service) {}

    public function clientes()
    {
        return $this->service->clientes();
    }

    public function cadastroCliente(string $Id)
    {   
        $userID = IdValueObject::fromPlain($Id);
        return $this->service->cadastroCliente($userID);
    }
}
