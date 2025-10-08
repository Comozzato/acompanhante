<?php

namespace App\Modules\Anunciante\Services;

use App\Behaviors\CpfBehaviors;
use App\Http\Resources\Anuncios;
use App\Models\Posts;
use App\Modules\Watermark\Services\Strategies\TypeMediaValueEnum;
use App\Modules\Watermark\Services\WatermarkStrategy;
use App\Services\AnuncioApiService;
use App\Services\S3ImageGalleryService;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rules\Enum;
use Log;

class AnuncianteService
{

    public function __construct(private AnuncioApiService $api) {}

    public function getAnuncioCpf()
    {
        $user = auth_user();
        if (empty($user->cpf)) {
            return response()->json(['message' => 'CPF do usuário não encontrado'], 400);
        }
        $cpf = new CpfBehaviors($user->cpf, false);
       
        $postsApi = $this->api->getAnuncionsCpf($cpf);
        if (empty($postsApi)) {
            return [];
        }
        $this->sincronizarAnunciosPorCpf($postsApi, $user);
        return Anuncios::collection($postsApi);
    }
    public function getAnuncioAdminCpf($cpf)
    {
        $postsApi = $this->api->getAnuncionsCpf($cpf);
        if (empty($postsApi)) {
            return [];
        }

        return Anuncios::collection($postsApi);
    }

    public function getDados($id)
    {
        return $this->api->getAnuncioDados($id);
    }

    public function postDados($id, array $dados)
    {
        return $this->api->postAnuncioDados($id, $dados);
    }

    public function sincronizarAnunciosPorCpf($postsApi, $user)
    {
        $user_id = $user->id;
        foreach ($postsApi as $postData) {
            // 2. Verifica se o post já existe no banco
            $post = Posts::where('id', $postData['id'])
                ->where('user_id', $user_id)
                ->first();
            if ($post) {
                // 3. Atualiza os dados caso tenha mudado
                $post->update([
                    'nome' => $postData['title'],
                    'cidade' => $postData['cidadeatual'],
                    'imgcapa' => $postData['imgcapa'],
                    'imgevidencias' => $postData['imgevidencias'],
                    'imgatualizadas' => $postData['imgatualizadas'],
                ]);
            } else {
                // 4. Cria novo post se não existir
                Posts::create([
                    'user_id' => $user_id,
                    'nome' => $postData['title'],
                    'id' => $postData['id'],
                    'cidade' => $postData['cidadeatual'],
                    'imgcapa' => $postData['imgcapa'],
                    'imgevidencias' => $postData['imgevidencias'],
                    'imgatualizadas' => $postData['imgatualizadas'],
                ]);
            }
        }
    }
}
