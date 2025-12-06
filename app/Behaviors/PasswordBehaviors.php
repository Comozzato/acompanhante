<?php

namespace App\Behaviors;

use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;

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

    private function validate(string $password, string $passwordConfirmation): void
    {
        $data = [
            'password' => $password,
            'password_confirmation' => $passwordConfirmation,
        ];

        $rules = [
            'password' => [
                'required',
                'string',
                'min:' . self::MIN_LENGTH,
                'confirmed', // compara com password_confirmation
                'regex:/[A-Z]/',               // pelo menos uma letra maiúscula
                'regex:/[a-z]/',               // pelo menos uma letra minúscula
                'regex:/[0-9]/',               // pelo menos um número
                'regex:' . self::SPECIAL_CHARACTERS, // caractere especial
            ],
        ];

        $messages = [
            'password.required' => 'A senha é obrigatória.',
            'password.min' => "A senha deve ter pelo menos " . self::MIN_LENGTH . " caracteres.",
            'password.confirmed' => 'As senhas não coincidem.',
            'password.regex' => 'A senha deve conter letras maiúsculas, minúsculas, números e um caractere especial.',
        ];

        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            $message = $validator->messages()->first();
            throw new HttpResponseException(response()->json(['message' => $message], 400));
        }
    }

    public function getValue(): string
    {
        return $this->password;
    }
}
