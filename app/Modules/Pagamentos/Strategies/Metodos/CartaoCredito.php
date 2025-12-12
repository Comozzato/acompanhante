<?php

declare(strict_types=1);

namespace App\Modules\Pagamentos\Strategies\Metodos;

use App\Models\Produto;
use App\Models\User;
use App\Modules\Pagamentos\Strategies\Interfaces\PagamentoCartaoCredito;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Money;

class CartaoCredito implements PagamentoCartaoCredito
{
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

        $formatter = new DecimalMoneyFormatter(new ISOCurrencies());
        $preco =  $formatter->format(Money::BRL($produto->preco));

        $valor = Money::BRL($preco);
        $payload = array_filter([
            'customer' => $user->asaas_customer_id,
            'billingType' => 'CREDIT_CARD',
            'value' =>  $valor->getAmount(),
            'dueDate' => $data['dueDate'],
            'installmentCount' => $data['installmentCount'] > 1 ? $data['installmentCount'] : null,
            'installmentValue' => 0,
            
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
}
