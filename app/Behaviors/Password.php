<?php

namespace App\Behaviors;

use InvalidArgumentException;

final class Password
{
    private string $hash;

    private const MIN_LENGTH = 8;
    private const REGEX_SPECIAL = '/[!@#$%^&*(),.?":{}|<>]/';

    private function __construct(string $hash)
    {
        $this->hash = $hash;
    }

    /**
     * Cria a senha a partir do texto plano (registro / troca de senha)
     */
    public static function fromPlain(string $plainPassword): self
    {
        self::validate($plainPassword);

        return new self(
            password_hash($plainPassword, PASSWORD_BCRYPT)
        );
    }

    /**
     * Cria a senha a partir do hash salvo no banco (login)
     */
    public static function fromHash(string $hash): self
    {
        return new self($hash);
    }

    private static function validate(string $password): void
    {
        if (strlen($password) < self::MIN_LENGTH) {
            throw new InvalidArgumentException(
                'Password must be at least ' . self::MIN_LENGTH . ' characters.'
            );
        }

        if (!preg_match(self::REGEX_SPECIAL, $password)) {
            throw new InvalidArgumentException(
                'Password must contain at least one special character.'
            );
        }
    }

    public function verify(string $plainPassword): bool
    {
        return password_verify($plainPassword, $this->hash);
    }

    public function hash(): string
    {
        return $this->hash;
    }
}

