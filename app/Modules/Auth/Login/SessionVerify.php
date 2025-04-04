<?php

declare(strict_types=1);

namespace App\Modules\Auth\Login;

use App\Models\SessionModel;
use Auth;

class SessionVerify
{
    public function verifyAndDeleteSession()
    {
        $user = Auth::user();
        $userAgent = request()->header('User-Agent', 'Desconhecido');

        // 🔎 Verifica se já existe uma sessão para este usuário no mesmo dispositivo
        $existingSession = SessionModel::where('user_id', $user->id)
            ->where('user_agent', $userAgent)
            ->first();

        if ($existingSession) {
            // Remove a sessão antiga para evitar múltiplos logins no mesmo dispositivo
            $existingSession->delete();
        }
    }
}