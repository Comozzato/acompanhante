<?php

namespace App\Gates;

use App\Models\User;
use Illuminate\Support\Facades\Request;

class Authentic
{
    public static function Auth()
    {
        $userId = Request::instance()->attributes->get('user_id');
        if (!$userId) {
            return null;
        }
        return User::with('unreadNotifications')->find($userId);
    }
}
