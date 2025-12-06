<?php

declare(strict_types=1);

namespace App\Modules\Auth\Register;

use App\Models\User;

class RegisterUser
{

    public function create(UserDto $userDto)
    {
        User::create($userDto->toArray());
    }
}