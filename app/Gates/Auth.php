<?php

use App\Models\Feed;
use Illuminate\Support\Facades\Gate;

return function () {
    Gate::define('admin', function ($user) {
        if(!$user)
            return false;
        return in_array($user->role, ['admn']);
    });

    Gate::define('post-limit', function ($user, string $tipoMidia, string $postId) {

        $countToday = Feed::where('user_id', $user->id)
            ->where('post_id', $postId)
            ->where('tipo_arquivo', $tipoMidia)
            ->whereDate('created_at', now()->toDateString())
            ->count();

        if ($countToday >= 1) {
            throw new \Illuminate\Http\Exceptions\HttpResponseException(response()->json(['message' => "Você já atingiu o limite diário para {$tipoMidia}."], 400));
        };
    });
};
