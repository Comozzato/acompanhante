<?php

namespace App\Modules\Auth\Register;

use App\Comportamentos\Cpf;
use App\Comportamentos\Email;
use App\Comportamentos\Password;

class User
{
    private Cpf $cpf;
    private Email $email;
    private Password $password;
    public function __construct(
        Cpf $cpf,
        Password $password,
        Email $email
    ) {
        $this->cpf = $cpf;
        $this->email = $email;
        $this->password = $password;
    }

    public function toArray(): array
    {
        return [
            'cpf' => $this->cpf->getValue(),
            'password' => $this->password->getValue(),
            'email' => $this->email->getValue(),
        ];
    }
}
