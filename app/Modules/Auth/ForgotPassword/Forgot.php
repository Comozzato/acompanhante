<?php

declare(strict_types=1);

namespace App\Modules\Auth\ForgotPassword;

use App\Behaviors\EmailAddress;
use App\Behaviors\EmailBehaviors;
use App\Behaviors\Password;
use App\Behaviors\PasswordBehaviors;
use App\Models\User;

class Forgot
{

    public function __construct(private VerifyCode $verifyCode) {}

    public function resetPassword(EmailAddress $email, Password $password): void
    {
        User::where('email', $email->value())
            ->update(['password' => $password->hash()]);
    }

    public function forgot(?string $code = null, EmailAddress $email, Password $password): void
    {
        $this->verifyCode->verify($email, $code);
        $this->verifyCode->deleteCode($email);
        $this->resetPassword($email, $password);
    }
}
