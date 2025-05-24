<?php

namespace App\Behaviors;

use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Exceptions\HttpResponseException;

class PasswordBehaviors
{
    private string $password;
    const MIN_LENGTH = 6;
    const SPECIAL_CHARACTERS = '/[!@#$%^&*]/';
    public function __construct(string $password, string $passwordConfimation)
    {
        $this->validate($password, $passwordConfimation);

        $this->password = $this->hashPassword($password);
    }

    private function hashPassword(string $password): string
    {
        return Hash::make($password);
    }

    private function validate(string $password, string $passwordConfimation): void
    {
        if (strlen($password) < self::MIN_LENGTH) {
            throw new HttpResponseException(response([
                'password' => ["A senha deve ter pelo menos " . self::MIN_LENGTH . " caracteres."]
            ], 422));
        }
        if (!preg_match('/[A-Z]/', $password)) {
            throw new HttpResponseException(response([
                'password' => ["A senha deve conter pelo menos uma letra maiúscula."]
            ], 422));
        }
        if (!preg_match('/[a-z]/', $password)) {
            throw new HttpResponseException(response([
                'password' => ["A senha deve conter pelo menos uma letra minúscula."]
            ], 422));
        }
        if (!preg_match('/[0-9]/', $password)) {
            throw new HttpResponseException(response([
                'password' => ["A senha deve conter pelo menos um número."]
            ], 422));
        }
        if (!preg_match(self::SPECIAL_CHARACTERS, $password)) {
            throw new HttpResponseException(response([
                'password' => ["A senha deve conter pelo menos um caractere especial."]
            ], 422));
        }
        if ($password !== $passwordConfimation) {
            throw new HttpResponseException(response([
                'password' => ['as senhas não coincidem']
            ], 422));
        }
    }

    public function getValue(): string
    {
        return $this->password;
    }
}