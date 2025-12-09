<?php

declare(strict_types=1);

namespace App\Modules\Pagamentos\Strategies\Metodos;

use App\Helpers\Asaas;
use App\Models\Produto;
use App\Models\User;
use App\Modules\Pagamentos\Strategies\Interfaces\Pagamentopix;
use Carbon\Carbon;

class Pix implements Pagamentopix
{   

    // funcao para gerar cobrança pix
    // escrito pelo comozzato em 05/11/2025
    // atualizado em 07/11/2025
    public function gerarCobranca(array $data)
    {   
        // busca os dados do usuário e do produto
        $user =  User::find($data['user_id'])->first();
        if (! $user) {
            throw new \RuntimeException('Usuário não encontrado');
        }       
        $produto = Produto::find($data['produto_id'])->first();
        // monta o payload para criar a cobrança pix na api do asaas
        if (! $produto) {
            throw new \RuntimeException('Produto não encontrado');
        }
        $payload = [
            'customer' => $user->asaas_customer_id,
            "description" => $produto->nome,
            "value" => $produto->preco,
            "dueDate" => Carbon::now()->addMinutes(30)->format('Y-m-d'),
            "billingType" => 'PIX'
        ];
        
        $asaas = asaas::init();

        try {
            // cria a cobrança pix na api do asaas
        $responseCreatePix =  $asaas->post('/v3/lean/payments', $payload);
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

        // Retorna a resposta da criação da cobrança Pix
        // deve retorna os dados de qr code e numero copia e cola

        return [$responseQrCodeNumeroCopiaCola , $responseCreatePixDecode ];
    }
}
