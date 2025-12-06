<?php

declare(strict_types=1);

namespace App\Modules\Auth\Register;

use App\Behaviors\Cpf;
use App\Behaviors\EmailAddress;
use App\Behaviors\Password;
use App\Behaviors\PasswordBehaviors;
use App\Models\User;

class UserDto
{


    public function __construct(
        //private NameBehaviors $name,
        private Cpf $cpf,
        private EmailAddress $email,
        private Password $password
    ) {

    }

    public function toArray(): array
    {   

        return [
            'cpf' => $this->cpf->getValue(),
            'email' => $this->email->value(),
            'password' => $this->password->hash(),
        ];
    }

}