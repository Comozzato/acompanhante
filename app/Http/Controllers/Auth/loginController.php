<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;


use App\Http\Requests\LoginRequest;
use App\Modules\Auth\Login\Authenticate;
use App\Modules\Auth\Login\CreateToken;


class loginController extends Controller
{

    public function __construct(
        private Authenticate $authenticate,
        private CreateToken $createToken
    ) {

    }
    public function login(LoginRequest $request)
    {
        $this->authenticate->auth($request);
        
        return $this->createToken->generate();
    }
}
