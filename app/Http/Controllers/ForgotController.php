<?php

namespace App\Http\Controllers;

use App\Behaviors\EmailAddress;
use App\Behaviors\Password;
use App\Behaviors\PasswordBehaviors;
use App\Http\Requests\ResetPasswordRequest;
use App\Modules\Auth\ForgotPassword\Forgot;
use App\Modules\Auth\ForgotPassword\SendCodeForUser;
use App\Modules\Auth\ForgotPassword\VerifyCode;
use Illuminate\Http\Request;

class ForgotController extends Controller
{
    //

    public function __construct(private Forgot $forgot, private VerifyCode $verifyCode, private SendCodeForUser $sendCodeForUser) {}

    public function sendCode(Request $request)
    {
        $dataRequest = $request->only('email');
        $this->sendCodeForUser->sendCode(new EmailAddress($dataRequest['email']));
        return response()->json(['message' => 'Código enviado com sucesso']);
    }

    public function verifyCode(Request $request)
    {
        $dataRequest = $request->only('code', 'email');
        $this->verifyCode->verify(new EmailAddress($dataRequest['email']), $dataRequest['code']);
        return response()->json(['message' => 'Código verificado com sucesso']);
    }

    public function forgot(Request $request)
    {
        
        $dataRequest = $request->only('code', 'email', 'password', 'password_confirmation');
        $this->forgot->forgot(
            $dataRequest['code'],
            new EmailAddress($dataRequest['email']),
            Password::fromPlain($dataRequest['password'])
        );

        return response()->json(['message' => 'Senha alterada com sucesso']);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $dataRequest = $request->validated();

        $user = auth_user();

        $this->forgot->resetPassword(
            new EmailAddress($user->email),
            Password::fromPlain($dataRequest['password'])
        );
        return response()->json(['message' => 'Senha alterada com sucesso']);
    }
}
