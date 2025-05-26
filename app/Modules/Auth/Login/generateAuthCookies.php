<?php

declare(strict_types=1);

namespace App\Modules\Auth\Login;
use App\Models\User;


use DomainException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Cookie;

class GenerateAuthCookies
{
    protected $secury;
    private $access_expire;
    private $refresh_expire;
    
    public function __construct(private SessionVerify $sessionVerify, private AccessToken $accessToken, private RefreshToken $refreshToken, private CreateSession $createSession)
    {
        $this->secury = config('services.login.security');
        $this->access_expire = config('services.token.access_expire');
        $this->refresh_expire = config('services.token.refresh_expire');
    }

    public function generate(User $user): JsonResponse
    {
        if (!$user instanceof User) {
            throw new HttpResponseException(response()->json(['message' => 'o usuario não foi devidamente authenticado'], 401));
        }
        $this->sessionVerify->verifyAndDeleteSession($user);
        $access_token = $this->accessToken->getAccessToken($user);
        $refresh_token = $this->refreshToken->getRefreshToken($user);
        $this->createSession->create($user, $access_token, $refresh_token);

        return response()
            ->json(['message' => 'Login bem-sucedido'])
            ->withCookie($this->createCookiesAcccessToken($access_token))
            ->withCookie($this->createCookiesRefreshToken($refresh_token));
        
        // return response()
        //     ->json([
        //         'message' => 'Login bem-sucedido',
        //         'access_token' => $access_token,
        //         'refresh_token' => $refresh_token
        //     ]);
    }
    
    public function createCookiesAcccessToken(string $access_token): Cookie
    {

        return cookie(
            name: 'access_token',
            value: $access_token,
            minutes: $this->access_expire,
            secure: $this->secury,
            httpOnly: false,
            sameSite: 'none'
        );
    }

    public function createCookiesRefreshToken(string $refresh_token): Cookie
    {

        return cookie(
            name: 'refresh_token',
            value: $refresh_token,
            minutes: 60 * 24 * $this->access_expire, // 7 dias
            secure: $this->secury,
            httpOnly: false,
            sameSite: 'none'
        );
    }
}