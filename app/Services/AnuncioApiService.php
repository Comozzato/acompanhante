<?php
declare(strict_types=1);

namespace App\Services;

use App\Behaviors\CpfBehaviors;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Http;
use PhpParser\JsonDecoder;

class AnuncioApiService
{
    private string $user;
    private string $pass;
    private string $url;
    private string $token;

    public function __construct()
    {
        $this->user = config('services.anuncio_api.user');
        $this->pass = config('services.anuncio_api.pass');
        $this->url = config('services.anuncio_api.url');
        $this->token = base64_encode("{$this->user}:{$this->pass}");
    }

    public function getTodosOsAnucios()
    {
        $endpoint = rtrim($this->url, '/') . "/wp-json/meusanuncios/v1/busca/?cpf={$cpf->getValue()}";
        $headers = [
            'Authorization' => 'Basic ' . $this->token,
        ];
        $response = Http::withHeaders($headers)->get($endpoint);

        if ($response->failed()) {
            $body = $response->json();
            $message = $body['message'] ?? 'Erro ao obter dados do anúncio';
            throw new HttpResponseException(response(['message' => $message], $response->status()));
        }

        return $response->json();   
    }

    public function getAnuncionsCpf(CpfBehaviors $cpf)
    {   

        $endpoint = rtrim($this->url, '/') . "/wp-json/meusanuncios/v1/busca/?cpf={$cpf->getValue()}";
        $headers = [
            'Authorization' => 'Basic ' . $this->token,
        ];
        $response = Http::withHeaders($headers)->get($endpoint); 
        if ($response->failed()) {
            $body = $response->json();
            $message = $body['message'] ?? 'Erro ao obter dados do anúncio';
            throw new HttpResponseException(response(['message' => $message], $response->status()));
        }
        return $response->json();
    }
    public function getAnuncioDados(int|string $id): array
    {
        $endpoint = rtrim($this->url, '/') . "/wp-json/anuncios/v1/anuncio/{$id}/dados";
        $headers = [
            'Authorization' => 'Basic ' . $this->token,
        ];
        $response = Http::withHeaders($headers)->get($endpoint);
        if ($response->failed()) {
            $body = $response->json();
            $message = $body['message'] ?? 'Erro ao obter dados do anúncio';
            throw new HttpResponseException(response(['message' => $message], $response->status()));
        }

        return $response->json();
    }

    public function postAnuncioDados(int|string $id, $data): array
    {
        $endpoint = rtrim($this->url, '/') . "/wp-json/anuncios/v1/anuncio/{$id}/dados";
        $headers = [
            'Authorization' => 'Basic ' . $this->token,
        ];
        $response = Http::withHeaders($headers)->post($endpoint, $data);

        if ($response->failed()) {
            $body = $response->json();
            $message = $body['message'] ?? 'Erro ao atualizar dados do anúncio';
            throw new HttpResponseException(response(['message' => $message], $response->status()));
        }

        return $response->json();
    }

    public function postMidiaDados(int|string $id, $data): array
    {
        $endpoint = rtrim($this->url, '/') . "/wp-json/anuncios/v1/anuncio/{$id}/midia";
        $headers = [
            'Authorization' => 'Basic ' . $this->token,
        ];
        $response = Http::withHeaders($headers)->post($endpoint, $data);

        if ($response->failed()) {
            $body = $response->json();
            $message = $body['message'] ?? 'Erro ao obter dados do anúncio';
            throw new HttpResponseException(response(['message' => $message], $response->status()));
        }

        return $response->json();
    }
}