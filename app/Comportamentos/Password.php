<?php

namespace App\Comportamentos;

use Illuminate\Http\Exceptions\HttpResponseException;
use InvalidArgumentException;


class Password
{
    private string $password;
    private string $passwordConfimation;
    const MIN_LENGTH = 6;
    const SPECIAL_CHARACTERS = '/[!@#$%^&*]/';
    public function __construct(string $password, string $passwordConfimation)
    {
        $this->validate($password, $passwordConfimation);
        
        $this->password = $this->hashPassword($password);
    }

    private function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    private function validate(string $password, string $passwordConfimation): void
    {   
        if (strlen($password) < self::MIN_LENGTH) {
            throw new HttpResponseException(response(['message' => "A senha deve ter pelo menos " . self::MIN_LENGTH . " caracteres."], 400));
        }
        if (!preg_match('/[A-Z]/', $password)) {
            throw new HttpResponseException(response(['message' => "A senha deve conter pelo menos uma letra maiúscula."], 400));
        }

        if (!preg_match('/[a-z]/', $password)) {
            throw new HttpResponseException(response(['message' => "A senha deve conter pelo menos uma letra minúscula."], 400));
        }

        if (!preg_match('/[0-9]/', $password)) {
            throw new HttpResponseException(response(['message' => "A senha deve conter pelo menos um número."], 400));
        }

        if (!preg_match(self::SPECIAL_CHARACTERS, $password)) {
            throw new HttpResponseException(response(['message' => "A senha deve conter pelo menos um caractere especial (!@#$%^&*)."], 400));
        }
        if($password !== $passwordConfimation)
        {
            throw new HttpResponseException(response(['message' => "As senhas não conferem."], 400));
        }
    }

    public function getValue(): string
    {
        return $this->password;
    }
}
