<?php

declare(strict_types=1);

namespace App\Modules\Auth\Session;

use App\Models\SessionModel;

class RefreshSession
{
    public function refreshTimeSession(SessionModel $session)
    {
        if ($session->refresh_expires_at > now()->timestamp) {
            $session->update([
                'access_expires_at' => now()->addMinutes(15)->timestamp,
            ]);
            return true;
        }
        $session->delete();
        return false;
    }
}
