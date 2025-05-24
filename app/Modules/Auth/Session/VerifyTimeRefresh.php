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
    private string $subToken;
    public function verify(string $refreshToken): bool
    {
        if (!$refreshToken) {
            throw new HttpResponseException(response(['message' => 'Não autenticado. Sessão não encontrada.'], Response::HTTP_UNAUTHORIZED));
        }
        $key = env('JWT_SECRET', 'your-secret-key');
        $decoded = JWT::decode($refreshToken, new Key($key, 'HS256'));
        $tokenData = json_decode(json_encode($decoded), true); // Converte para objeto
        if ($tokenData['token_type'] !== 'refresh_token') {
            throw new HttpResponseException(response()->json(['message' => 'Token inválido.'], Response::HTTP_UNAUTHORIZED));
        }
        $session = Session::where('user_id', $tokenData['sub'])->where('refresh_token', $refreshToken)->first();
        if (!$session) {
            throw new HttpResponseException(response()->json(['message' => 'Não autenticado. Sessão não encontrada.'], response::HTTP_UNAUTHORIZED));
        }
        $this->subToken = $tokenData['sub'];
        $this->validate($tokenData, $session);
        return true;
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
