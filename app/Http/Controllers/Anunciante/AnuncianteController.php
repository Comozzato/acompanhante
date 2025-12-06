<?php

namespace App\Http\Controllers\Anunciante;

use App\Behaviors\CpfBehaviors;
use App\Http\Controllers\Anunciante\Requests\AnuncianteDadosRequest;

use App\Http\Controllers\Controller;
use App\Http\Resources\AnuncioMidias;
use App\Models\User;
use App\Modules\Anunciante\Services\AnuncianteService;
use App\Services\S3ImageGalleryService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AnuncianteController extends Controller
{

    public function __construct(private AnuncianteService $service) {}

    public function getMyAnuncios()
    {
        $user = auth_user();
        return $this->service->getAnuncioCpf($user);
    }
    public function getAnuncioCpf(request $request)
    {
        Gate::forUser(auth_user())->allows('admin');
        $data = $request->input('cpf');
        if (empty($data)) {
            return response()->json(['message' => 'CPF não informado'], 400);
        }
        $cpf = new CpfBehaviors($data, false);
        return $this->service->getAnuncioAdminCpf($cpf);
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

    public function GetAllAnunciantesForAdminForPosts(Request $request)
    {
        //Gate::forUser(auth_user())->allows('admin');
        $anuncios = $this->service->GetAllAnunciantesForAdmin($request->all());
        return AnuncioMidias::collection($anuncios);
    }
}
