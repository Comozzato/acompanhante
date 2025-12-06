<?php

namespace App\Http\Controllers\Auth;

use App\Behaviors\Cpf;
use App\Behaviors\EmailAddress;
use App\Behaviors\Password;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Modules\Auth\Register\RegisterUser;
use App\Modules\Auth\Register\UserDto;
class RegisterController extends Controller
{
    //  
    protected RegisterUser $register;
    public function __construct(RegisterUser $registerUser)
    {
        $this->register = $registerUser;
    }

    public function register(RegisterRequest $request)
    {   
        
        $dataRequest = $request->validated();

        $cpf = new Cpf($dataRequest['cpf']);
        $email = new EmailAddress($dataRequest['email']);
       
        
        $userDto = new UserDto(
            $cpf,
            $email,
            Password::fromPlain($dataRequest['password'])
        );
        
        $this->register->create($userDto);
        return response()->json(['message' => 'Register successfully'], 201);
    }
}
