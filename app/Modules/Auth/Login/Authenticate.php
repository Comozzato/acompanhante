<?php

declare(strict_types=1);

namespace App\Modules\Auth\Login;

use App\Http\Requests\LoginRequest;
use Auth;
use Illuminate\Http\Exceptions\HttpResponseException;


class Authenticate
{
    public function auth(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        $credentials = $request->validated();

        if (!Auth::attempt($credentials, false)) {
            throw new HttpResponseException(response(['message' => 'Credenciais inválidas'], 401));
        }
    }
}