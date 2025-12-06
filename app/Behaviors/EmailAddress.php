<?php

namespace App\Behaviors;

use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

final class EmailAddress
{
    private string $value;

    public function __construct(string $email)
    {     
        $email = trim($email);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('O e-mail não é válido.');
        }

        $this->value = $email;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
