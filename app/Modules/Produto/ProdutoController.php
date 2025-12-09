<?php


declare(strict_types=1);

namespace App\Modules\Produto;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProdutoController extends Controller
{
    public function __construct(private ProdutoService $service) {}

    public function listarProdutos()
    {
        return $this->service->listarProdutos();
    }

    public function criarProduto(Request $request)
    {
        $data = $request->input();
        return $this->service->criarProduto($data);
    }

    public function atualizarProduto(int $id, array $data)
    {
        return $this->service->atualizarProduto($id, $data);
    }

    public function deletarProduto(int $id)
    {
        return $this->service->deletarProduto($id);
    }
}