<?php

declare(strict_types=1);

namespace App\Modules\Pagamentos;

use App\Helpers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PagamentoController extends Controller
{
    public function __construct(private PagamentoService $service) {}

    public function pagamento(Request $request) {
        
    }
}
