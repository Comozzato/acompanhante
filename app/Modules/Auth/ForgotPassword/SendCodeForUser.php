<?php

declare(strict_types=1);

namespace App\Modules\Auth\ForgotPassword;

use App\Comportamentos\Email;
use App\Mail\ForgotPassword;
use Cache;
use Mail;

class SendCodeForUser
{

    public function __construct()
    {

    }

    public function sendCode(Email $email): void
    {
        $email = $email->getEmailIfExists();

        $code = $this->createCode($email);
        
        Mail::to($email)->send(new ForgotPassword($code));

    }

    private function createCode(string $email): string
    {
        $code = (string) rand(100000, 999999);
        Cache::store('file')->put('forgot_password_code_' . $email, $code, 60 * 30); // 30 minutos de expiração
        return $code;

    }
}