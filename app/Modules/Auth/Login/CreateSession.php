<?php

declare(strict_types=1);
namespace App\Modules\Auth\Login;
use App\Models\Session;
use App\Models\User;
use Str;

class CreateSession
{
    public function __construct(private Session $sessionModel)
    {

    }

    public function create(User $user, string $access_token, string $refresh_token)
    {
        Session::insert([
            'id' => Str::random(40), // Compatível com o padrão Laravel para sessions
            'user_id' => $user->id,
            'access_token' => $access_token,
            'refresh_token' => $refresh_token,
            'ip_address' => request()->ip(),
            'last_activity' => now()->timestamp,
            'payload' => request()->userAgent(),
            'user_agent' => request()->header('User-Agent', 'Desconhecido'),
        ]);
    }
}