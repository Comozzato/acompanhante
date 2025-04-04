<?php

declare(strict_types=1);

namespace App\Modules\Auth\ForgotPassword;

use App\Comportamentos\Email;
use App\Comportamentos\Password;
use App\Models\User;

class Forgot
{

    public function __construct(private VerifyCode $verifyCode)
    {
    }

    public function forgot(string $code, Email $email, Password $password): void
    {
        $this->verifyCode->verify($email, $code);
        $this->verifyCode->deleteCode($email);
        User::where('email', $email->getEmailIfExists())
            ->update(['password' => $password->getValue()]);
    }
}