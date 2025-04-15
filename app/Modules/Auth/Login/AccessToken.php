<?php

declare(strict_types=1);
namespace App\Modules\Auth\Login;

use App\Models\User;
use Crypt;
use DomainException;

class AccessToken
{
    public function __construct()
    {

    }

    public function getAccessToken(User $user): string
    {   
        if (!$user instanceof User) {
            throw new DomainException(response()->json(['message' => 'o usuario não foi devidamente authenticado'], 401));
        }
        $payload = [
            'sub' => $user->id,
            'name' => $user->name,
            'role' => $user->role,
            'iat' => now()->timestamp,
            'exp' => now()->addMinutes(15)->timestamp,
            'ip' => request()->ip(),
            'user_agent' => request()->header('User-Agent', 'Desconhecido')
        ];

        return Crypt::encryptString(json_encode($payload));
    }


}