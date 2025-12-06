<?php

declare(strict_types=1);

namespace App\Modules\Auth\Register;

use App\Behaviors\CpfBehaviors;
use App\Behaviors\EmailBehaviors;
use App\Behaviors\NameBehaviors;
use App\Behaviors\PasswordBehaviors;


class UserDto
{


    public function __construct(
        //private NameBehaviors $name,
        private CpfBehaviors $cpf,
        private EmailBehaviors $email,
        private PasswordBehaviors $password
    ) {

    }

    public function toArray(): array
    {
        return [
            //'name' => $this->name->getValue(),
            'cpf' => $this->cpf->getCpfNoUsed(),
            'email' => $this->email->getValue(),
            'password' => $this->password->getValue(),
        ];
    }

}