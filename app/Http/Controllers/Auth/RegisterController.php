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
        info($request);
        $dataRequest = $request->only('name', 'cpf', 'email', 'password', 'password_confirmation');
        
        $userDto = new UserDto(
            //  new NameBehaviors($dataRequest['name']),
            new CpfBehaviors($dataRequest['cpf']),
            new EmailBehaviors($dataRequest['email']),
            new PasswordBehaviors($dataRequest['password'], $dataRequest['password_confirmation'])
        );

        $this->register->create($userDto);
        return response(['message' => 'Register successfully'], 201);
    }
}
