<?php

declare(strict_types=1);

namespace App\Behaviors;
use Illuminate\Http\Exceptions\HttpResponseException;

class NameBehaviors
{

    public function __construct(private string $name)
    {

        $this->validate($name);

    }

    private function validate(string $name): void
    {
        $name = trim($name);

        // Não permite espaço no início ou no fim
        if ($name === '' || $name[0] === ' ' || substr($name, -1) === ' ') {
            throw new HttpResponseException(response([
                'name' => ['O nome não pode começar ou terminar com espaço.']
            ], 422));
        }

        // Não permite mais de 50 caracteres
        if (mb_strlen($name) > 50) {
            throw new HttpResponseException(response([
                'name' => ['O nome não pode ter mais que 50 caracteres.']
            ], 422));
        }

        // Não permite símbolos (apenas letras, números e espaço)
        if (!preg_match('/^[\p{L}\p{N} ]+$/u', $name)) {
            throw new HttpResponseException(response([
                'name' => ['O nome não pode conter símbolos.']
            ], 422));
        }
    }

    public function getValue(): string
    {
        return $this->name;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
