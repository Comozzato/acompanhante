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

    public function __construct(private TokenVerifier $tokenVerifier)
    {

    }
    public function handle(Request $request, Closure $next)
    {

        $accessToken = $request->cookie('access_token');
        if (empty($accessToken)) {
            return response()->json(['message' => 'Não autenticado.'], Response::HTTP_UNAUTHORIZED);
        }
        $payload = $this->tokenVerifier->verify($accessToken); // sua função de validação
        $request->attributes->set('user_id', $payload['sub']);
        $request->attributes->set('role', $payload['role']);
        return $next($request);

    }
}

