<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Modules\Auth\Session\CreateNewAccessToken;
use App\Modules\Auth\Session\TokenVerifier;
use App\Modules\Auth\Session\VerifyTimeAccessToken;
use App\Modules\Auth\Session\VerifyTimeRefresh;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;


class VerifyAccessToken
{

    public function __construct(private TokenVerifier $tokenVerifier, private VerifyTimeRefresh $verifyTimeRefresh, private CreateNewAccessToken $createNewAccessToken)
    {

    }
    public function handle(Request $request, Closure $next)
    {

        $accessToken = $request->cookie('access_token');
        //$refreshToken = $request->cookie('refresh_token');
        if (empty($accessToken)) {
            return response()->json(['message' => 'Não autenticado.'], Response::HTTP_UNAUTHORIZED);
        }
        $payload = $this->tokenVerifier->verify($accessToken); // sua função de validação
        $request->attributes->set('user_id', $payload['sub']);
        $request->attributes->set('role', $payload['role']);
        return $next($request);
        // if ($this->tokenVerifier->verify($accessToken)) {
        //     return $next($request);
        // }

        // if ($this->verifyTimeRefresh->verify($refreshToken)) {
        //     $userId = $this->verifyTimeRefresh->getSubToken(); // <- pega o sub
        //     $user = User::find($userId); // <- busca o usuário
        //     $cookie = $this->createNewAccessToken->handle($user);
        //     $response = $next($request);
        //     return $response->withCookie($cookie);
        // }

        // return response()->json(['message' => 'Não autenticado. Sessão expirada.'], Response::HTTP_UNAUTHORIZED);
    }
}

