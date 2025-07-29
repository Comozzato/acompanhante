<?php

declare(strict_types=1);

namespace app\Http\Controllers\Feed;

use App\Enums\ImagemFeed;
use App\Models\Feed;
use App\Models\Midia;
use App\Modules\PostImgFeed\Services\Treantment;
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

    public function __construct(private Treantment $treantment)
    {
        // Você pode adicionar middleware ou outras configurações aqui, se necessário.
    }

    public function index(Request $request)
    {
        $user = $request->user(); // usuário autenticado, se houver

        $arquivos = Storage::disk('s3')->files('0195fda0-1218-70d8-88a5-02b62cc5a11e/posts');

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
        $query->orderByDesc('publicado_em');

        // Paginação simples
        $posts = $query->paginate($request->input('limit', 10));

        return response()->json($posts);
    }



    public function post(Request $request)
    {
        $dataRequest = $request->input();
        $inputFilePostFeed = $request->file('file');
        $paths = $this->treantment->processImageFeed($inputFilePostFeed);
        $post = [
            'user_id' => auth_user()->id,
            'tipo' => 'imagem',
            'titulo' => $dataRequest['titulo'],
            'conteudo' => $dataRequest['conteudo'],
            'ativo' => true,
            'publicado_em' => now()->setTimezone('America/Sao_Paulo')
        ];
        $feed = Feed::create($post);
        foreach ($paths as $path) {
            Midia::create([
                'feed_id' => $feed->id,
                'midia' => $path
            ]);
        }
        return response()->json(['message' => 'criado com sucesso'], 200);
    }

    public function getImagemFeed(Request $request)
    {
        $path = $request->input('path');
    
        return Storage::disk('s3')->get($path); // Retorna o conteúdo da imagem
    }
}
