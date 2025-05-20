<?php
namespace App\Modules\Anunciante\Controllers;

use App\Modules\Anunciante\Requests\AnuncianteDadosRequest;
use App\Modules\Anunciante\Requests\AnuncianteMidiaRequest;
use App\Modules\Anunciante\Services\AnuncianteService;
use Illuminate\Http\Request;

class AnuncianteController
{
  
    public function __construct(private AnuncianteService $service)
    {
      
    }

    public function getDados($id)
    {
        return $this->service->getDados($id);
    }

    public function postDados(AnuncianteDadosRequest $request, $id)
    {
        return $this->service->postDados($id, $request->validated());
    }

    public function postMidia(Request $request, $id)
    {
        $file = $request->file('file');
        $tipo = $request->input('tipo'); 
        return $this->service->postMidia($id, $file, $tipo);
    }
}
