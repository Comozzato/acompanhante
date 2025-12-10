<?php

declare(strict_types=1);    

namespace  App\Modules\Produto;

use App\Models\Produto;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Money;

class ProdutoService
{
    public function __construct(Private Produto $produto) {

    }

    
    public function listarProdutos(): array
    {   
        return $this->produto->all()->toArray();
    }

    public function criarProduto(array $data): Produto
    {   

        $money =  Money::BRL((int) $data['preco'])->getAmount();
        $data['preco'] = $money;
        return $this->produto->create($data);
    }

    public function atualizarProduto(int $id, array $data): Produto
    {
        $produto = $this->produto->findOrFail($id);
        $produto->update($data);
        return $produto;
    }

    public function deletarProduto(int $id): bool
    {
        $produto = $this->produto->findOrFail($id);
        return $produto->delete();
    }
    
}

