<?php

declare(strict_types=1);

namespace App\Modules\Auth\Session;

use App\Models\SessionModel;
use Crypt;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class VerifyTimeAccessToken
{
    public function verify(string $accessToken): bool
    {
        if (!$accessToken) {
            throw new HttpResponseException(response(['message' => 'Não autenticado. Sessão não encontrada.'], Response::HTTP_UNAUTHORIZED));
        }
        $decryptedToken = Crypt::decryptString($accessToken);
        $tokenData = json_decode($decryptedToken, true);
        info($tokenData);
        if ($tokenData['exp'] > now()->timestamp) {
            return true;
        }
        if (!SessionModel::where('id', $tokenData['user_id'])->where('access_token', $tokenData['access_token'])->exists()) {
            throw new HttpResponseException(response()->json(['message' => 'Não autenticado. Sessão não encontrada.'], response::HTTP_UNAUTHORIZED));
        }
        
        return false;
    }

}