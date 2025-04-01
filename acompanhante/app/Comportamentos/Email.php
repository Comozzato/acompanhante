<?php

namespace App\Comportamentos;

use http\Exception\InvalidArgumentException;

class Email
{
    private  string $email;
    public function __construct(string $email)
    {
        $this->validate($email);

        $this->email = $email;
    }


    private function validate(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('O email não é valido');
        }
    }


    public function getValue(): string
    {
        return $this->email;
    }
}
