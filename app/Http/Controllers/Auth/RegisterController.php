<?php

namespace App\Http\Controllers\Auth;

use App\Behaviors\CpfBehaviors;
use App\Behaviors\EmailBehaviors;
use App\Behaviors\NameBehaviors;
use App\Behaviors\PasswordBehaviors;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Auth\Register\RegisterUser;
use App\Modules\Auth\Register\UserDto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RegisterController extends Controller
{
    //  
    protected RegisterUser $register;
    public function __construct(RegisterUser $registerUser)
    {
        $this->register = $registerUser;
    }

    public function register(Request $request)
    {

        $dataRequest = $request->only('cpf', 'email', 'password', 'password_confirmation');
        $cpf = new CpfBehaviors($dataRequest['cpf']);
        $email = new EmailBehaviors($dataRequest['email']);
        $password = new PasswordBehaviors($dataRequest['password'], $dataRequest['password_confirmation']);
        $userDto = new UserDto(
            //  new NameBehaviors($dataRequest['name']),
            $cpf,
            $email,
            $password
        );

        $this->register->create($userDto);
        return response()->json(['message' => 'Register successfully'], 201);
    }


    public function updateEmail(Request $request)
    {
        Gate::forUser(auth_user())->allows('admin');

         $request->validate([
        'lastEmail' => 'required|email',
        'newEmail'  => 'required|email'
    ]);

    // verifica se o NOVO email já existe
    if (User::where('email', $request->newEmail)->exists()) {
        return response()->json(['message' => 'email já registrado'], 422);
    }

    $user = User::where('email', $request->lastEmail)->first();

    if (!$user) {
        return response()->json(['message' => 'usuário não encontrado'], 404);
    }

    $user->update([
        'email' => $request->newEmail
    ]);

    return response()->json(['message' => 'successfully'], 200);
}
}