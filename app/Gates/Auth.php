<?php

use App\Models\Feed;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Request;


return function () {
    Gate::define('admin', function ($user) {

        if (!in_array($user->role, ['admn'])) {
            throw new \Illuminate\Http\Exceptions\HttpResponseException(response()->json(['message' => 'Acesso negado'], 401));
        }
    });

    Gate::define('post-limit', function ($user, string $tipoMidia) {

        $countToday = Feed::where('user_id', $user->id)
            ->where('tipo_arquivo', $tipoMidia)
            ->whereDate('created_at', now()->toDateString())
            ->count();

        if ($countToday >= 1) {
            throw new \Illuminate\Http\Exceptions\HttpResponseException(response()->json(['message' => "Você já atingiu o limite diário para {$tipoMidia}."], 400));
        };
    });
};
