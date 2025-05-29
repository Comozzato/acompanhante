<?php

declare(strict_types=1);

namespace App\Modules\Auth\Session;

use App\Models\User;
use App\Modules\Auth\Login\GenerateAuthCookies;
use DomainException;
use Symfony\Component\HttpFoundation\Cookie;

class CreateNewAccessToken
{
    public function __construct(private GenerateAuthCookies $authCookies)
    {

    }


    public function handle(User $user): Cookie
    {
        if (!$user) {
            throw new DomainException(response()->json(['message' => 'Usuário não autenticado.'], 401));
        }

        return $this->authCookies->createCookiesAcccessToken($user);
    }
}