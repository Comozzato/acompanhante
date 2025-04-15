<?php

declare(strict_types=1);

namespace App\Modules\Auth\Session;

use App\Models\SessionModel;
use Crypt;
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
        $decryptedToken = Crypt::decryptString($refreshToken);
        $tokenData = json_decode($decryptedToken, true); // retorna um array associativo
        if ($tokenData['token_type'] !== 'refresh_token') {
            throw new HttpResponseException(response()->json(['message' => 'Token inválido.'], Response::HTTP_UNAUTHORIZED));
        }
        $session = SessionModel::where('user_id', $tokenData['sub'])->where('refres_token', $tokenData['refres_token'])->first();
        if (!$session) {
            throw new HttpResponseException(response()->json(['message' => 'Não autenticado. Sessão não encontrada.'], response::HTTP_UNAUTHORIZED));
        }
        $this->subToken = $tokenData['sub'];
        $this->validate($tokenData, $session);
        return true;
    }


    private function validate(array $refreshToken, SessionModel $session): void
    {

        if ($refreshToken['exp'] < now()->timestamp) {
            throw new HttpResponseException(
                response()->json(['message' => 'Token expirado.'], Response::HTTP_UNAUTHORIZED)
            );
        }
        // Verifica se IP e user agent correspondem
        if (
            $session->ip !== request()->ip() ||
            $session->user_agent !== request()->header('User-Agent', 'Desconhecido')
        ) {
            $session->delete(); // invalida imediatamente
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
