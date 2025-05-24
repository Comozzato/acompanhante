<?php
namespace App\Http\Controllers\Anunciante;

use App\Behaviors\CpfBehaviors;
use App\Http\Controllers\Anunciante\Requests\AnuncianteDadosRequest;
use App\Http\Controllers\Anunciante\Requests\AnuncianteMidiaRequest;
use App\Modules\Anunciante\Services\AnuncianteService;
use App\Services\S3ImageGalleryService;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Response;

class AnuncianteController
{

    public function __construct(private AnuncianteService $service)
    {

    }

    public function getAnuncioCpf(request $request)
    {

        $data = $request->input('cpf');
        $cpf = new CpfBehaviors($data);
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

    public function postMidia(Request $request, $id)
    {
        $file = $request->file('file');
        $tipo = $request->input('tipo');
        return $this->service->postMidia($id, $file, $tipo);

    }

    public function getImagemFeed(Request $request)
    {
        $path = $request->input('path');

        $s3 = new S3ImageGalleryService();
        $path = '13144/gallery/efd4bb57-f078-4156-a2bd-015e9ddf69cf.jpeg';
        $imageContent = $s3->getImage($path); // deve retornar binário da imagem

        return response($imageContent, 200)
            ->header('Content-Type', 'image/jpeg'); // ajuste se for PNG etc;
    }
}
