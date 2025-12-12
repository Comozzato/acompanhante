<?php

declare(strict_types=1);

namespace App\Modules\Pagamentos;

use App\Helpers\Api;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class PagamentoController extends Controller
{
    public function __construct(private PagamentoService $service) {}

    public function gerarCobranca($type)
    {   
        // seleciona o tipo de pagamento
        $tipoPagamento = TipoPagamento::from($type);
        // seleciona o tipo de formulario
        $requestClass = $tipoPagamento->TipoRequest();
        // coleta os dados do formulario verificado
        $inputData =  $requestClass->validated();
        return  $this->service->gerarCobranca($tipoPagamento, $inputData);
    }
}
