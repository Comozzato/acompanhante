<?php

declare(strict_types=1);

namespace App\Modules\Auth\ForgotPassword;


use App\Behaviors\EmailBehaviors;
use App\Mail\ForgotPassword;

use Illuminate\Http\Client\HttpClientException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class SendCodeForUser
{

    public function __construct()
    {

    }

    public function sendCode(EmailBehaviors $email): void
    {
        $code = $this->createCode($email->getEmailIfExists());
        $this->sendEmail($email, $code);
    }

    private function createCode(string $email): string
    {
        $code = (string) rand(100000, 999999);
        Cache::store('file')->put('forgot_password_code_' . $email, $code, 60 * 30); // 30 minutos de expiração
        return $code;
    }

    private function sendEmail(EmailBehaviors $email, string $code)
    {
        try {
            Mail::to($email->getValue())->send(new ForgotPassword($code));
        } catch (\Exception $e) {
            throw new HttpClientException($e->getMessage(), 400);
        }
    }
}