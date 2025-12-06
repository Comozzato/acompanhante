<?php

declare(strict_types=1);

namespace App\Modules\Auth\Login;
use App\Models\User;

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
            ->json([
                'message' => 'Login bem-sucedido',
                'notifications' => $user->unreadNotifications()->count(),
                'access_token' => $access_token,
                'refresh_token' => $refresh_token
            ]);
    }
}
