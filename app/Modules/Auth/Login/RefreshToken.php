<?php

declare(strict_types=1);
namespace App\Modules\Auth\Login;
use App\Models\User;
use App\Modules\Auth\Login\SessionVerify;
use Crypt;
use DomainException;
use Str;

class RefreshToken
{
    public function __construct(private SessionVerify $sessionVerify)
    {
    }

    public function getRefreshToken(User $user): string
    {   
        if (!$user instanceof User) {
            throw new DomainException(response()->json(['message' => 'o usuario não foi devidamente authenticado'], 401));
        }
        $payload = [
            'token_type' => 'refresh_token', // define que esse é um refresh token
            'sub' => $user->id, // "subject" – identifica o dono do token
            'iat' => now()->timestamp, // issued at – quando foi criado
            'exp' => now()->addDays(7)->timestamp, // expiração do refresh token
            'jti' => Str::uuid()->toString(), // unique ID do token (para revogação ou blacklist)
            'ip' => request()->ip(), // IP de onde o token foi emitido
            'user_agent' => request()->header('User-Agent', 'Desconhecido'), // para validar se é o mesmo dispositivo
        ];

        return Crypt::encryptString(json_encode($payload));
    }
}