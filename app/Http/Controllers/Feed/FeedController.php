<?php

declare(strict_types=1);

namespace app\Http\Controllers\Feed;

use App\Enums\ImagesFeed;
use App\Modules\PostImgFeed\Services\Treantment;
use Illuminate\Http\File;
use Illuminate\Http\Request;

class FeedController extends \App\Http\Controllers\Controller
{

    /**
     * Exemplo de método que poderia ser adicionado ao FeedController.
     * Este método poderia ser usado para retornar um feed de posts.
     *
     * @return \Illuminate\Http\Response
     */

    public function __construct(Private Treantment $treantment)
    {
        // Você pode adicionar middleware ou outras configurações aqui, se necessário.
    }
    public function index()
    {
        // Aqui você pode implementar a lógica para retornar o feed de posts.
        return response()->json(['message' => 'Feed de posts']);
    }

     public function post(Request $request)
    {
        $dataRequest = $request->input(['titulo','conteudo']);
        $inputFilePostFeed = $request->file('file');
        $outputFileMaster =  $this->treantment->processImageFeed($inputFilePostFeed, ImagesFeed::MASTER);
    }
}
