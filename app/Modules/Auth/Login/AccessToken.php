<?php

declare(strict_types=1);
namespace App\Modules\Auth\Login;

use App\Models\User;
use Crypt;
use DomainException;
use Firebase\JWT\JWT;
use Illuminate\Http\Exceptions\HttpResponseException;

class AccessToken
{

    private $access_expire;
    private readonly string $key;
    public function __construct()
    {
        $this->access_expire = config('services.token.access_expire');
        $this->key = base64_decode(config('services.token.key'));
    }

    public function getAccessToken(User $user): string
    {
        if (!$user instanceof User) {
            throw new HttpResponseException(response()->json(['message' => 'o usuario não foi devidamente authenticado'], 401));
        }

        $accessExpiresTimesTamp = now()->addminutes((int) $this->access_expire)->timestamp;
        $now = now()->timestamp;

        $payload = [
            'iss' => config('app.url'),
            'sub' => $user->id,
            'name' => $user->name,
            'role' => $user->role,
            'iat' => $now,
            'exp' => $accessExpiresTimesTamp,
            'ip' => request()->ip(),
            'user_agent' => request()->header('User-Agent', 'Desconhecido')
        ];


        return JWT::encode($payload, $this->key, 'HS256');
    }


}
