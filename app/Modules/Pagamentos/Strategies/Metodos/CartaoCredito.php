<?php

declare(strict_types=1);

namespace App\Modules\Pagamentos\Strategies\Metodos;

use App\Modules\Pagamentos\Strategies\Interfaces\PagamentoCartaoCredito;
use Money\Money;

class CartaoCredito implements PagamentoCartaoCredito
{
    public function gerarCobranca(array $data)
    {   

       
        $valor = Money::BRL($data['value']);
        $payload = array_filter([
            'customer' => $data['customer'],
            'billingType' => 'CREDIT_CARD',
            'value' =>  number_format($valor->getAmount() / 100, 2, '.', ''),
            'dueDate' => $data['dueDate'],
            //'description' => $data['description'] ?? 'Pagamento via cartão de crédito',
            'installmentCount' => $data['installmentCount'] > 1 ? $data['installmentCount'] : null,
            'installmentValue' => $this->calcularValorParcela($data['installmentCount'], $data['value']),
            'creditCard' => [
                'holderName' => $data['creditCard']['holderName'],
                'number' => $data['creditCard']['number'],
                'expiryMonth' => $data['creditCard']['expiryMonth'],
                'expiryYear' => $data['creditCard']['expiryYear'],
                'ccv' => $data['creditCard']['ccv'],
            ],

            'creditCardHolderInfo' => [
                'name' => $data['creditCardHolderInfo']['name'],
                'email' => $data['creditCardHolderInfo']['email'],
                'cpfCnpj' => $data['creditCardHolderInfo']['cpfCnpj'],
                'postalCode' => $data['creditCardHolderInfo']['postalCode'],
                'addressNumber' => $data['creditCardHolderInfo']['addressNumber'],
                'addressComplement' => $data['creditCardHolderInfo']['addressComplement'] ?? null,
                'phone' => $data['creditCardHolderInfo']['phone'] ?? null,
                'mobilePhone' => $data['creditCardHolderInfo']['mobilePhone'] ?? null,
            ],
            'remoteIp' => $data['remoteIp'] ?? request()->ip(),
        ]);

        $response =  $this->asaasApi->post('/v3/lean/payments', $payload);

        return $response;
    }

    private function calcularValorParcela(int $parcelas = 1, int $value)
    {
        if ($parcelas > 1) {
            $valorParcela = $value / $parcelas;
        }
        return $valorParcela ?? null;
    }
    private function calcularTotalPorValorParcela($value, int $valueParcela) {}
}
