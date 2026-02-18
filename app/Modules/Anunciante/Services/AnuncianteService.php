<?php

namespace App\Modules\Anunciante\Services;

use App\Behaviors\CpfBehaviors;
use App\Http\Resources\Anuncios;
use App\Models\Feed;
use App\Models\Posts;
use App\Models\User;
use App\Services\AnuncioApiService;

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
            return 0;
        }
        $this->sincronizarAnunciosPorCpf($postsApi, $user);
    }

    public function getAnuncioAdminCpf($cpf)
    {
        $postsApi = $this->api->getAnuncionsCpf($cpf);
        if (empty($postsApi)) {
            return 0;
        }
        if (User::where('cpf', $cpf->getValue())->exists()) {
            $user = User::where('cpf', $cpf->getValue())->first();
            $this->sincronizarAnunciosPorCpf($postsApi, $user);
            return ['anuncios' => posts::where('user_id', $user->id)->get()->toArray(), 'profile' => [
                'email' => $user->email
            ]];
        }

        return ['anuncios' => Anuncios::collection($postsApi), 'cadastrado' => 0];
    }

    public function getDados($id)
    {
        return $this->api->getAnuncioDados($id);
    }

    public function getDadosCompleto($id)
    {
        return $this->api->getAnuncioDadosCompleto($id);
    }

    public function postDados($id, array $dados)
    {
        return $this->api->postAnuncioDados($id, $dados);
    }

    public function postAnuncioDadosCompleto($id, array $dados)
    {
        return $this->api->postAnuncioDadosCompleto($id, $dados);
    }

    private function sincronizarAnunciosPorCpf($postsApi, $user)
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
                    'status' => $postData['status'],
                    'url' => $postData['url'],
                    'cidade_virtual' => $postData['temvirtual'] ?? 0,
                    'cidades_virtuais' => $postData['cidadevirtual'] ?? null,
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
                    'status' => $postData['status'],
                    'url' => $postData['url'],
                    'cidade_virtual' => $postData['temvirtual'] ?? 0,
                    'cidades_virtuais' => $postData['cidadevirtual'] ?? null,
                ]);
            }
        }
    }


    public function atualizarPublicacoesAnunciantes($user)
    {
        $postsApi = $this->api->getAnuncionsCpf(new CpfBehaviors($user->cpf, false));
        $this->sincronizarAnunciosPorCpf($postsApi, $user);
    }

    public function GetAllAnunciantesForAdmin()
    {
        $query = Feed::query()
            ->with([
                'posts_info' => fn($q) => $q->select('id', 'nome', 'cidade', 'url'),
                'midia',
            ])
            ->whereHas('post', fn($q) => $q->city(request('city')))
            ->aprovado()
            ->orderByDesc('publicado_em');

        return $query->paginate(request('per_page', 10));
    }
}
