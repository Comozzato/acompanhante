<?php

namespace App\Providers;

use App\Behaviors\Cpf;
use App\Behaviors\CpfBehaviors;
use App\Models\Feed;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{

    public function boot(): void
    {

        $this->registerPolicies();

        Gate::define('ver-cpf', function (User $user, Cpf $cpfBehaviors) {
            // Se for admin, pode tudo

            if ($user->role === 'admn') {
                return true;
            }

            // Se for antc, só se o CPF for o mesmo
            if ($user->role === 'antc' && $user->cpf === $cpfBehaviors->getValue()) {
                return true;
            }

            return false;
        });


      
    }
}
