<?php

namespace App\Behaviors;

use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;

class EmailBehaviors
{
    private string $email;
    public function __construct(?string $email)
    {
        $this->validate($email);
        $this->email = $email;
    }


    private function validate(string $email): void
    {
        $data = ['email' => $email];
        $rules = [
            'email' => [
                'required',
                'email',
                'unique:users,email', // Verifica se já existe no banco
            ],
        ];

        $messages = [
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'O e-mail não é válido.',
            'email.unique' => 'Este e-mail já está em uso.',
        ];

        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            $message = $validator->messages()->first();
            throw new HttpResponseException(response()->json(['message' => $message], 400));
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
