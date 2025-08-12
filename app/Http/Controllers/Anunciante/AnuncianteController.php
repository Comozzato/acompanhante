<?php
namespace App\Http\Controllers\Anunciante;

use App\Behaviors\CpfBehaviors;
use App\Http\Controllers\Anunciante\Requests\AnuncianteDadosRequest;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Anunciante\Services\AnuncianteService;
use App\Services\S3ImageGalleryService;

use Illuminate\Http\Request;

class AnuncianteController extends Controller
{

    public function __construct(private AnuncianteService $service)
    {

    }

    public function getMyAnuncios()
    {
        $user = User::find(Request()->attributes->get('user_id'));
        $cpf = new CpfBehaviors($user->cpf, false);
        return $this->service->getAnuncioCpf($cpf);
    }
    public function getAnuncioCpf(request $request)
    {

        $data = $request->input('cpf');

        $cpf = new CpfBehaviors($data, false);
        // if (Gate::denies('ver-cpf',$cpf)) {
        //     abort(403, 'Você não tem permissão para acessar este CPF.');
        // }
        return $this->service->getAnuncioCpf($cpf);

    }
    public function getDados($id)
    {
        return $this->service->getDados($id);
    }

    public function postDados(AnuncianteDadosRequest $request, $id)
    {

        return $this->service->postDados($id, $request->validated());
    }

    // public function postMidia(Request $request, $id)
    // {
    //     $file = $request->file('file');
    //     $tipo = $request->input('tipo');
    //     return $this->service->postMidia($id, $file, $tipo);

    // }

    public function getImagemFeed(Request $request)
    {
        $path = $request->input('path');

        $imageContent = S3ImageGalleryService::getImage($path); // deve retornar binário da imagem
        if (is_null($imageContent)) {
           
            return response()->json(['message' => 'Imagem não encontrada'], 404);
        }
        return response()->json(['image' => $imageContent]);
    }
}
