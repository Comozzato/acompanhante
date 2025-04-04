<?php

namespace App\Http\Controllers;

use App\Comportamentos\Email;
use App\Comportamentos\Password;
use App\Modules\Auth\ForgotPassword\Forgot;
use App\Modules\Auth\ForgotPassword\SendCodeForUser;
use App\Modules\Auth\ForgotPassword\VerifyCode;
use Illuminate\Http\Request;

class ForgotController extends Controller
{
    //

    public function __construct(private Forgot $forgot, private VerifyCode $verifyCode, private SendCodeForUser $sendCodeForUser)
    {
    }

    public function sendCode(Request $request)
    {
        $dataRequest = $request->only('email');
        $this->sendCodeForUser->sendCode(new Email($dataRequest['email']));
        return response()->json(['message' => 'Código enviado com sucesso']);
    }

    public function verifyCode(Request $request)
    {
        $dataRequest = $request->only('code', 'email');
        $this->verifyCode->verify(new Email($dataRequest['email']), $dataRequest['code']);
        return response()->json(['message' => 'Código verificado com sucesso']);
    }

    public function reset(Request $request)
    {
        $dataRequest = $request->only('code', 'email', 'password', 'password_confirmation');
       
        $this->forgot->forgot(
            $dataRequest['code'],
            new Email($dataRequest['email']),
            new Password($dataRequest['password'], $dataRequest['password_confirmation'])
        );

        return response()->json(['message' => 'Senha alterada com sucesso']);
    }
}
