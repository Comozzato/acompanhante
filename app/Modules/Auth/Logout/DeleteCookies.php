<?php

declare(strict_types=1);

namespace App\Modules\Auth\Logout;

use App\Models\SessionModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class DeleteCookies
{
    public function logout(Request $request)
    {


        $sessionId = $request->cookie('session_id');
        if ($sessionId) {
            SessionModel::where('id', $sessionId)->delete();
        }
      
        Auth::logout();
        return response()->json(['message' => 'Logout realizado com sucesso'])
            ->withCookie(cookie()->forget('session_id'));
    }
}