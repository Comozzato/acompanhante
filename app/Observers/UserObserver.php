<?php

declare(strict_types=1);

namespace App\Observers;

use App\Events\UserRegistered;
use App\Models\User;

class UserObserver
{
    //
    public function created(User $user): void
    {
        info('User created observer triggered');
        UserRegistered::dispatch($user);
    }
}