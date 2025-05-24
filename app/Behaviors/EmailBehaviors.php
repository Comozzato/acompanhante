<?php

namespace App\Behaviors;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;

class EmailBehaviors
{
    private string $email;
    public function __construct(string $email)
    {
        $this->validate($email);
        $this->email = $email;
    }


    private function validate(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new HttpResponseException(response([
                'email' => ['O email não é válido']
            ], 422));
        }
        $this->used($email);
    }

    private function used($email)
    {
        if (User::where('email', '=', $email)->exists()) {
            throw new HttpResponseException(response([
                'email' => ['Email em uso']
            ], 422));
        }
    }

    public function getValue(): string
    {
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