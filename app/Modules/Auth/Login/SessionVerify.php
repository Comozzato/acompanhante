<?php

declare(strict_types=1);

namespace App\Modules\Auth\Login;

use App\Models\Session;
use App\Models\User;

class SessionVerify
{   
    public function __construct(private Session $sessionModel) 
    {

    }
    public function verifyAndDeleteSession(User $user)
    {
        $userAgent = request()->header('User-Agent', 'Desconhecido');
        // 🔎 Verifica se já existe uma sessão para este usuário no mesmo dispositivo
        $existingSession = $this->sessionModel->where('user_id', $user->id)
            ->where('user_agent', $userAgent)
            ->first();
        
        if ($existingSession) {
            $existingSession->delete();
        }
    }
}