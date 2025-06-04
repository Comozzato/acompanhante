<?php

declare(strict_types=1);

namespace App\Modules\Auth\Session;


use App\Models\Session;
use Crypt;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class VerifyTimeRefresh
{
    private string $key;
    private string $subToken;
    public function __construct()
    {
        $this->key = base64_decode(config('services.token.key'));
        // Você pode inicializar qualquer dependência aqui, se necessário
    }

    public function verify(string $refreshToken): void
    {
        if (!$refreshToken) {
            throw new HttpResponseException(response(['message' => 'Não autenticado. Sessão não encontrada.'], Response::HTTP_UNAUTHORIZED));
        }

        $payload = $this->decodePayload($refreshToken);
        if ($payload['token_type'] !== 'refresh_token') {
            throw new HttpResponseException(response()->json(['message' => 'Token inválido.'], Response::HTTP_UNAUTHORIZED));
        }
        $session = Session::where('user_id', $payload['sub'])->where('refresh_token', $refreshToken)->first();
        if (!$session) {
            throw new HttpResponseException(response()->json(['message' => 'Não autenticado. Sessão não encontrada.'], response::HTTP_UNAUTHORIZED));
        }
        $this->subToken = $payload['sub'];
        $this->validate($payload, $session);
    }

    private function decodePayload(string $jwt): array
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            throw new HttpResponseException(response(['message' => 'Token inválido.'], Response::HTTP_UNAUTHORIZED));
        }
        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
        if (!$payload) {
            throw new HttpResponseException(response(['message' => 'Payload inválido.'], Response::HTTP_UNAUTHORIZED));
        }
        return $payload;
    }
    private function validate(array $refreshToken, Session $session): void
    {
        
        if ($refreshToken['exp'] < now()->timestamp) {
            throw new HttpResponseException(
                response()->json(['message' => 'Token expirado.'], Response::HTTP_UNAUTHORIZED)
            );
        }
        if (
            $session->ip_address !== request()->ip() ||
            $session->user_agent !== request()->header('User-Agent', 'Desconhecido')
        ) {
            //$session->delete(); // invalida imediatamente
            throw new HttpResponseException(
                response()->json(['message' => 'Inconsistência na origem da requisição.'], Response::HTTP_UNAUTHORIZED)
            );
        }

    }

    public function getSubToken(): string
    {
        return $this->subToken;
    }
}
