<?php

namespace App\Http\Controllers\Anunciante;

use App\Behaviors\CpfBehaviors;
use App\Http\Controllers\Anunciante\Requests\AnuncianteDadosRequest;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostDadosAnuncianteAdmin;
use App\Http\Resources\AnuncioMidias;
use App\Models\Posts;
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
        $this->service->getAnuncioCpf($user);

        $data = Posts::where('user_id', $user->id)->get();
        return $data;
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

    public function getDadosCompleto($id)
    {   
        if(Gate::forUser(auth_user())->allows('admin'))
        {
            return $this->service->getDadosCompleto($id);
        }
        return response()->json(['message' => 'Acesso negado'], 401);
    }
    public function postDados(AnuncianteDadosRequest $request, $id)
    {
        return $this->service->postDados($id, $request->validated());
    }

    public function atualizarDadosAnuncianteAdmin(PostDadosAnuncianteAdmin $request,  $id)
    {   
        
        return $this->service->postAnuncioDadosCompleto($id, $request->validated());
        
        return response()->json(['message' => 'Acesso negado'], 401);
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
        Gate::forUser(auth_user())->allows('admin');
        $anuncios = $this->service->GetAllAnunciantesForAdmin($request->all());
        return AnuncioMidias::collection($anuncios);
    }
}
