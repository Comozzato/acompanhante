<?php

declare(strict_types=1);
namespace App\Modules\Auth\Login;
use App\Models\SessionModel;
use App\Models\User;

class CreateSession
{
    public function __construct(private SessionModel $sessionModel)
    {

    }

    public function create(User $user, string $access_token, string $refresh_token)
    {
        SessionModel::create([
            'user_id' => $user->id,
            'access_token' => $access_token,
            'refresh_token' => $refresh_token,
            'expires_at' => now()->addMinutes(15),
            'ip_address' => request()->ip(),
            'last_activity' => now()->timestamp,
            'payload' => request()->userAgent(),
            'user_agent' => request()->header('User-Agent', 'Desconhecido'),
        ]);
    }
}