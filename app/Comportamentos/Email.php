<?php

namespace App\Comportamentos;

use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;

class Email
{
    private readonly string $email;
    public function __construct(string $email)
    {
        $this->validate($email);
        $this->email = $email;
    }


    private function validate(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new HttpResponseException(response(['message' => 'Email inválido'], 400));
        }
    }

    public function getValue(): string
    {
        if (User::where('email', $this->email)->exists()) {
            throw new HttpResponseException(response(['message' => 'Email já está em uso'], 400));
        }
        return $this->email;
    }


    public function getEmailIfExists()
    {
        if (User::where('email', $this->email)->exists()) {
            return $this->email;
        }
        throw new HttpResponseException(response(['message' => 'Email não foi encontrado'], 400));
    }
}
