<?php

namespace App\Http\Controllers\Auth;

use App\Behaviors\CpfBehaviors;
use App\Behaviors\EmailBehaviors;
use App\Behaviors\NameBehaviors;
use App\Behaviors\PasswordBehaviors;
use App\Http\Controllers\Controller;
use App\Modules\Auth\Register\RegisterUser;
use App\Modules\Auth\Register\UserDto;
use Illuminate\Http\Request;



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

        $dataRequest = $request->only('name', 'cpf', 'email', 'password', 'password_confirmation');
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
}
