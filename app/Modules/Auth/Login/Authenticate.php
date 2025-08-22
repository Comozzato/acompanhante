<?php

declare(strict_types=1);

namespace App\Modules\Auth\Login;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Hash;

class Authenticate
{
    public function auth(LoginRequest $request): User
    {

        $credentials = $request->only("email", "password");
        $credentials = $request->validated();

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw new HttpResponseException(response(['message' => 'Credenciais inválidas'], 400));
        }
        
        return $user;
    }


}
