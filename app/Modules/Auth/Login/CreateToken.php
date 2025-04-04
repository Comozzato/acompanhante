<?php

declare(strict_types=1);

namespace App\Modules\Auth\Login;

use App\Models\SessionModel;
use App\Models\User;
use Auth;
use Str;

class CreateToken
{
    public function __construct(private SessionVerify $sessionVerify)
    {

    }
    public function generate()
    {

        $this->sessionVerify->verifyAndDeleteSession(); // Verifica e remove sessões antigas;

        $user = Auth::user()->getAuthIdentifier();

        $session = SessionModel::create([
            'user_id' => $user,
            'access_token' => Str::random(60),
            'refresh_token' => Str::random(80),
            'expires_at' => now()->addMinutes(15), // Tempo de expiração do token
            'access_expires_at' => now()->addMinutes(15)->timestamp, // Access Token dura 15 minutos
            'refresh_expires_at' => now()->addDays(7)->timestamp, // Tempo do Refresh Token
            'ip_address' => request()->ip(),
            'last_activity' => now()->timestamp,
            'payload' => '',
            'user_agent' => request()->header('User-Agent', 'Desconhecido'),
        ]);


        return response()
            ->json(['message' => 'Login bem-sucedido'])
            ->withCookie(cookie(
                name: 'session_id',
                value: $session->id,
                minutes: 120,// Tempo correto
                secure: true,
                httpOnly: true,
                sameSite: 'strict'
            ));
    }
}