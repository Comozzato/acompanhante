<?php

namespace App\Modules\Auth\Register;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class Register
{
    protected \App\Models\User $model;
    public function __construct(\App\Models\User $model)
    {
        $this->model = $model;
    }
    public function registrarUser(User $user): void
    {   

        $newUser = $this->model->create($user->toArray());
        if (!$newUser)
            throw new Exception("Error creating user");
    }
}
