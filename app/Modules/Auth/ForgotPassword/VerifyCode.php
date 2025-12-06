<?php

declare(strict_types=1);

namespace App\Modules\Auth\ForgotPassword;

use App\Behaviors\EmailAddress;
use App\Behaviors\EmailBehaviors;


use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;


class VerifyCode
{
    public function verify(EmailAddress $email, string $code): void
    {
        $codeCacher = Cache::store('file')->get('forgot_password_code_' . $email->value());
        if ($code !== $codeCacher) {
            throw new HttpResponseException(response(['message' => 'Código inválido.'], Response::HTTP_UNAUTHORIZED));
        }
        
    }

    public function deleteCode(EmailAddress $email): void
    {
        Cache::store('file')->forget('forgot_password_code_' . $email->value());
    }
}