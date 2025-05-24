<?php

declare(strict_types=1);

namespace App\Modules\Auth\Session;

use App\Models\Session;
use Crypt;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class VerifyTimeAccessToken
{
    public function verify(string $accessToken): bool
    {
        if (!$accessToken) {
            throw new HttpResponseException(response(['message' => 'Não autenticado. Sessão não encontrada.'], Response::HTTP_UNAUTHORIZED));
        }
        $tokenParts = explode('.', $accessToken);
        if (count($tokenParts) !== 3) {
            throw new HttpResponseException(response(['message' => 'Token inválido.'], Response::HTTP_UNAUTHORIZED));
        }
        $payload = json_decode(base64_decode(strtr($tokenParts[1], '-_', '+/')), true);
    
        if (isset($payload['exp']) && $payload['exp'] > now()->timestamp) {
            return true;
        }
        if (!Session::where('id', $payload['sub'])->where('access_token', $accessToken)->exists()) {
            throw new HttpResponseException(response()->json(['message' => 'Não autenticado. Sessão não encontrada.'], response::HTTP_UNAUTHORIZED));
        }

        return false;
    }

}