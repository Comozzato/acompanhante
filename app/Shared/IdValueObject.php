<?php

namespace App\Modules\Shared\ValueObjects;

use Illuminate\Support\Str;
use InvalidArgumentException;

class IdValueObject
{
    private readonly string $id;

    public function __construct(string $id)
    {
        if (!Str::isUuid($id)) {
            throw new InvalidArgumentException('ID inválido');
        }
        $this->id = $id;
    }

    public function getValue(): string
    {
        return $this->id;
    }
}