<?php

namespace App\Behaviors;

use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;

class CpfBehaviors
{
    private string $cpf;

    public function __construct(string $cpf, bool $cpfUnique = true)
    {
        $this->validateCpf($cpf, $cpfUnique);
    }

    public function getValue(): string
    {
        return $this->cpf;
    }
    public function validateCpf(string $cpf, bool $unique = true): void
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);

        $data = ['cpf' => $cpf];

        $rules = [
            'cpf' => [
                'required',
                'digits:11',
                // Só adiciona a regra unique se $unique for true
                ...($unique ? ['unique:users,cpf'] : []),
                function ($attribute, $value, $fail) {
                    if (preg_match('/(\d)\1{10}/', $value)) {
                        return $fail('Formato do CPF inválido.');
                    }

                    for ($t = 9; $t < 11; $t++) {
                        for ($d = 0, $c = 0; $c < $t; $c++) {
                            $d += $value[$c] * (($t + 1) - $c);
                        }
                        $d = ((10 * $d) % 11) % 10;
                        if ($value[$c] != $d) {
                            return $fail('Formato do CPF inválido.');
                        }
                    }
                },
            ],
        ];

        $messages = [
            'cpf.required' => 'O CPF é obrigatório.',
            'cpf.digits'   => 'O CPF deve ter exatamente 11 dígitos.',
            'cpf.unique'   => 'Este CPF já está cadastrado.',
        ];

        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            $message = $validator->messages()->first();
            throw new HttpResponseException(response()->json(['message' => $message], 400));
        }

        $this->cpf = $cpf;
    }
    public function getCpfNoUsed(): string
    {
        $this->used($this->cpf);
        return $this->cpf;
    }
    private function used($cpf)
    {
        if (User::where('cpf', '=', $cpf)->exists()) {
            throw new HttpResponseException(response(
                [
                    'message' => 'CPF já cadastrado'
                ],
                400
            ));
        }
    }
}
