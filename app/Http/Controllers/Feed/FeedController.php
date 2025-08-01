<?php

declare(strict_types=1);

namespace app\Http\Controllers\Feed;

use App\Models\Feed;
use App\Modules\PostImgFeed\Services\PostServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class FeedController extends \App\Http\Controllers\Controller
{

    /**
     * Exemplo de método que poderia ser adicionado ao FeedController.
     * Este método poderia ser usado para retornar um feed de posts.
     *
     * @return \Illuminate\Http\Response
     */

    public function __construct(Private PostServices $service)
    {
        // Você pode adicionar middleware ou outras configurações aqui, se necessário.
    }

    public function index(Request $request)
    {
        $user = $request->user(); // usuário autenticado, se houver
        $query = Feed::query()->with(['anunciante', 'midia']);

        // Exemplo futuro: recomendação por algoritmo
        // if ($request->boolean('algoritmo') && $user) {
        //     // Aqui entraria o motor de recomendação
        //     //$query = Feed::recommendedForUser($user);
        // }

        // // Filtro por anunciantes seguidos
        // if ($request->boolean('seguindo') && $user) {
        //     $ids = $user->anunciantesSeguidos()->pluck('id');
        //     $query->whereIn('anunciante_id', $ids);
        // }
        // Filtro por categoria
        // if ($request->filled('categoria')) {
        //     $query->where('categoria', $request->input('categoria'));
        // }

        // Ordenar por mais recente

        // Paginação simples
        $posts = $query->paginate($request->input('limit', 10));

        return response()->json($posts);
    }

    public function indexByUser()
    {
        $query = Feed::query()
            ->where('user_id', auth_user()->id)
            ->with(['anunciante', 'midia'])
            ->orderByDesc('publicado_em');

        $query->orderByDesc('publicado_em');

        $posts = $query->paginate();

        return response()->json($posts);
    }

    public function post(Request $request)
    {
        info($request);
        $dataRequest = $request->input();
        $inputFilePostFeed = $request->file('file');
        $this->service->post($dataRequest, $inputFilePostFeed);
        return response()->json(['message' => 'criado com sucesso'], 200);
    }

    public function getImagemFeed(Request $request)
    {
        $path = $request->input('path');

        return Storage::disk('s3')->get($path); // Retorna o conteúdo da imagem
    }
}
