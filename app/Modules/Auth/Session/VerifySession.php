<?php

declare(strict_types=1);

namespace App\Modules\Auth\Session;

use App\Models\SessionModel;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class VerifySession
{
    public function __construct(private RefreshSession $refreshSession)
    {

    }
    public function verifyAccessTime(string $sessionId)
    {
        if (!$sessionId) {
            throw new HttpResponseException(response(['message' => 'Não autenticado. Sessão não encontrada.'],Response::HTTP_UNAUTHORIZED));
        }
        $session = SessionModel::where('id', $sessionId)->first();
        if (!$session) {
            throw new HttpResponseException(response()->json(['message' => 'Não autenticado. Sessão não encontrada.'], response::HTTP_UNAUTHORIZED));
        }

        if ($session->access_expires_at > now()->timestamp) {
            return true;
        }
        return $this->refreshSession->refreshTimeSession($session);
    }

}