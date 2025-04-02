<?php

namespace App\Comportamentos;

use App\Models\User;
use InvalidArgumentException;

class Cpf
{
    private string $cpf;

    public function __construct(string $cpf)
    {
        $this->validateCpf($cpf);
        $this->cpf = $cpf;
    }

    public function getValue(): string
    {
        return $this->cpf;
    }
    public function validateCpf(string $cpf): void
    {
        $cpf = preg_replace("/[^0-9]/is", '', $cpf);
        if (strlen($cpf) != 11) {
            throw new InvalidArgumentException('Comprimento do CPF inválido');
        }
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            throw new InvalidArgumentException('Formato do CPF inválido');
        }

        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                throw new InvalidArgumentException('CPF Inválido');
            }
        }

        $this->CpfIsUniqueUser($cpf);

    }

    private function CpfIsUniqueUser(string $cpf): void
    {
        if (User::where('cpf', '=', $cpf))
            throw new InvalidArgumentException('CPF já cadastrado', 400);
    }



}
