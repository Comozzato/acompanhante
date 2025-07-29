<?php

namespace App\Gates;

use App\Models\User;
use Illuminate\Support\Facades\Request;

class Authentic
{
    public static function Auth()
    {
        $userId = Request::instance()->attributes->get('user_id');

        if (!$userId) {
            return null; // ou lançar uma exceção, se preferir
        }

        $user = User::find($userId);

        if (!$user) {
            return null; // ou lançar uma exceção personalizada
        }

        $user = (object)[
            'role' => $user->role,
            'id' => $user->id,
            'email' => $user->email,
            'cpf' => $user->cpf,
        ];
        return $user;
    }
}
