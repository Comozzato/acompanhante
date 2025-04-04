<?php

namespace App\Http\Middleware;

use App\Modules\Auth\Session\VerifySession;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;


class AuthenticateBySessionCookie
{
    public function __construct(private VerifySession $verifySession)
    {

    }
    public function handle(Request $request, Closure $next)
    {

        $sessionId = $request->cookie('session_id');

        if ($this->verifySession->verifyAccessTime($sessionId)) {
            return $next($request);
        }
        return response()->json(['message' => 'Não autenticado. Sessão expirada.'], Response::HTTP_UNAUTHORIZED);
    }
}
