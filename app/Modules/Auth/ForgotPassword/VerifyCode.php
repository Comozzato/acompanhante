<?php

declare(strict_types=1);

namespace App\Modules\Auth\ForgotPassword;

use App\Comportamentos\Email;
use Cache;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;


class VerifyCode
{
    public function verify(Email $email, string $code): void
    {
        $codeCacher = Cache::store('file')->get('forgot_password_code_' . $email->getEmailIfExists());
        if ($code !== $codeCacher) {
            throw new HttpResponseException(response(['message' => 'Código inválido.'], Response::HTTP_UNAUTHORIZED));
        }
        
    }

    public function deleteCode(Email $email): void
    {
        Cache::store('file')->forget('forgot_password_code_' . $email->getEmailIfExists());
    }
}