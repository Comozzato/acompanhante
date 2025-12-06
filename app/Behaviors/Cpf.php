<?php

namespace App\Behaviors;

use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

final class Cpf
{
    private string $value;

    public function __construct(string $cpf)
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        $this->validateCpf($cpf);
        $this->value = $cpf;
    }

    public function validateCpf(string $cpf): void
    {

        if (preg_match('/(\d)\1{10}/', $cpf)) {
                throw new InvalidArgumentException('Formato do CPF inválido.', 400);
        }

        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                throw new InvalidArgumentException('Formato do CPF inválido.', 400);
            }
        }          
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
