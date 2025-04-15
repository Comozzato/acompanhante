<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;


use App\Http\Requests\LoginRequest;
use App\Modules\Auth\Login\Authenticate;
use App\Modules\Auth\Login\generateAuthCookies;
use Illuminate\Http\JsonResponse;


class loginController extends Controller
{

    public function __construct(
        private Authenticate $authenticate,
        private generateAuthCookies $createToken
    ) {

    }
    public function login(LoginRequest $request): JsonResponse
    {
        $user = $this->authenticate->auth($request);
        return $this->createToken->generate($user);
    }
}
