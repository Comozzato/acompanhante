<?php

namespace App\Http\Controllers\Auth;

use App\Comportamentos\Cpf;
use App\Comportamentos\Email;
use App\Comportamentos\Password;
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
    public function register(Request $request): Response
    {
        $dataRequest = $request->only('cpf', 'email', 'password');
        $cpf = new Cpf($dataRequest['cpf']);
        $password = new Password($dataRequest['password']);
        $email = new Email($dataRequest['email']);
        $user = new User(
            $cpf,
            $password,
            $email
        );
        $this->register->registrarUser($user);
        return response(['message' => 'Usuário registrado com sucesso'], 201);
    }
}
