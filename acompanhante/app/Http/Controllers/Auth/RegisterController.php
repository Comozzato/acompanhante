<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Register\Register;
use App\Modules\Auth\Register\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RegisterController extends Controller
{
    protected Register $register;

    public function __construct(Register $register)
    {
        $this->register = $register;
    }
    public function register(Request $request):Response
    {
        $dataRequest = $request->only('name', 'email', 'password');
        $user = new User($dataRequest['name'], $dataRequest['email'], $dataRequest['password']);
        $this->register->registrarUser($user);
        return response(['message' => 'Register successfully'],201);
    }
}
