<?php
namespace App\Modules\Anunciante\Services;

use App\Services\AnuncioApiService;

class AnuncianteService
{
    protected $api;
    public function __construct(AnuncioApiService $api)
    {
        $this->api = $api;
    }

    public function getDados($id)
    {
        return $this->api->getAnuncioDados($id);
    }

    public function postDados($id, array $dados)
    {
        return $this->api->postAnuncioDados($id, $dados);
    }

    public function postMidia($id, $file, $tipo)
    {
        // Aqui você pode tratar o upload e chamar postMidiaDados
        // Exemplo: $this->api->postMidiaDados($id, ...)
        return ['status' => 'ok', 'mensagem' => 'Upload não implementado'];
    }
}
