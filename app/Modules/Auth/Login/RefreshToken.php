<?php

declare(strict_types=1);
namespace App\Modules\Auth\Login;
use App\Models\User;
use App\Modules\Auth\Login\SessionVerify;
use Crypt;
use DomainException;
use Firebase\JWT\JWT;
use Illuminate\Http\Exceptions\HttpResponseException;
use Str;

class RefreshToken
{
    private $refresh_expire;
    private readonly string $key;
    public function __construct(private SessionVerify $sessionVerify)
    {
        $this->refresh_expire = config('services.token.refresh_expire');
        $this->key = config('services.token.key');
    }

    public function getRefreshToken(User $user): string
    {
        if (!$user instanceof User) {
            throw new HttpResponseException(response()->json(['message' => 'o usuario não foi devidamente authenticado'], 401));
        }
        $accessExpiresTimesTamp = now()->addminutes((int) $this->refresh_expire)->timestamp;
        $now = now()->timestamp;
        $payload = [
            'iss' => config('app.url'),
            'token_type' => 'refresh_token', // define que esse é um refresh token
            'sub' => $user->id, // "subject" – identifica o dono do token
            'iat' => $now, // issued at – quando foi criado
            'exp' =>  $accessExpiresTimesTamp, // expiração do refresh token
            'jti' => Str::uuid()->toString(), // unique ID do token (para revogação ou blacklist)
            'ip' => request()->ip(), // IP de onde o token foi emitido
            'user_agent' => request()->header('User-Agent', 'Desconhecido'), // para validar se é o mesmo dispositivo
        ];
        
        return JWT::encode($payload, $this->key, 'HS256');
    }
}