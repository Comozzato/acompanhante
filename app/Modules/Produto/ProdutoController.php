<?php


declare(strict_types=1);

namespace App\Modules\Produto;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProdutoRequest;
use Illuminate\Http\Request;

class ProdutoController extends Controller
{
    public function __construct(private ProdutoService $service) {}


    public function listarProdutos()
    {
      
        return $this->service->listarProdutos();
    }


    public function criarProduto(ProdutoRequest $request)
    {   
        $data = $request->validated();
        return $this->service->criarProduto($data);
    }


    public function atualizarProduto( $request, $id)
    {
        $data = $request->input();
        return $this->service->atualizarProduto($id, $data);
    }

    public function deletarProduto($id)
    {
        return $this->service->deletarProduto($id);
    }
}