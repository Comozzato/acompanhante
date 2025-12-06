<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //'name' => $this->faker->name,  // comente ou remova
            'cpf' => $this->gerarCpfValido(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    private function gerarCpfValido(): string
    {
        $nove = [];
        for ($i = 0; $i < 9; $i++) {
            $nove[] = random_int(0, 9);
        }

        // Calcula primeiro DV
        $soma = 0;
        for ($i = 0; $i < 9; $i++) {
            $soma += $nove[$i] * (10 - $i);
        }
        $resto = $soma % 11;
        $dv1 = ($resto < 2) ? 0 : 11 - $resto;

        // Calcula segundo DV
        $soma = 0;
        for ($i = 0; $i < 9; $i++) {
            $soma += $nove[$i] * (11 - $i);
        }
        $soma += $dv1 * 2;
        $resto = $soma % 11;
        $dv2 = ($resto < 2) ? 0 : 11 - $resto;

        return implode('', $nove) . $dv1 . $dv2;
    }
}
