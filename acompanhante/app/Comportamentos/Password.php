<?php

namespace App\Comportamentos;

use InvalidArgumentException;


class Password
{
    private string $password;
    const MIN_LENGTH = 6;
    const SPECIAL_CHARACTERS = '/[!@#$%^&*]/';
    public function __construct(string $password)
    {
        $this->validate($password);

        $this->password = $this->hashPassword($password);
    }

    private function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    private function validate(string $password):void
    {
        if (strlen($password) < self::MIN_LENGTH) {
            throw new InvalidArgumentException("A senha deve ter pelo menos " . self::MIN_LENGTH . " caracteres.");
        }

        if (!preg_match('/[A-Z]/', $password)) {
            throw new InvalidArgumentException("A senha deve conter pelo menos uma letra maiúscula.");
        }

        if (!preg_match('/[a-z]/', $password)) {
            throw new InvalidArgumentException("A senha deve conter pelo menos uma letra minúscula.");
        }

        if (!preg_match('/[0-9]/', $password)) {
            throw new InvalidArgumentException("A senha deve conter pelo menos um número.");
        }


        if (!preg_match(self::SPECIAL_CHARACTERS, $password)) {
            throw new InvalidArgumentException("A senha deve conter pelo menos um caractere especial (!@#$%^&*).");
        }
    }

    public function getValue():string
    {
        return $this->password;
    }
}
