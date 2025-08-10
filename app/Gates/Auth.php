<?php

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Request;


return function () {
        Gate::define('admin', function ($user) {

            if(!in_array($user->role, ['admn']))
            {
                throw new \Illuminate\Http\Exceptions\HttpResponseException(response()->json(['message'=>'Acesso negado'],401));
            }
        });
};