<?php

declare(strict_types=1);

namespace App\Modules\Auth\Logout;


use App\Models\Session;
use Crypt;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;


class DeleteCookies
{
    public function logout(Request $request)
    {

        $access_token = $request->cookie('access_token');
        $key = env('JWT_SECRET', 'your-secret-key');
        $decoded = JWT::decode($access_token, new Key($key, 'HS256'));
        $tokenData = json_decode(json_encode($decoded), true); // Converte para objeto
        $session = Session::where('user_id', $tokenData['sub'])->first();
        if (!$session) {
            throw new HttpResponseException(response(['message' => 'sessão nao encontrada'], 401));
        }
        $session->delete();
        return response()->json(['message' => 'Logout realizado com sucesso'])
            ->withCookie(cookie()->forget('access_tokens'))
            ->withCookie(cookie()->forget('refres_tokens'));
    }
}
