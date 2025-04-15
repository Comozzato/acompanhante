<?php

declare(strict_types=1);

namespace App\Modules\Auth\Logout;

use App\Models\SessionModel;
use Crypt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class DeleteCookies
{
    public function logout(Request $request)
    {

        $access_token = $request->cookie('access_token');
        info($access_token);
        $decryptedToken = Crypt::decryptString($access_token);
        $tokenData = json_decode($decryptedToken, true);
        SessionModel::where('user_id', $tokenData['sub'])->where('access_token', $access_token)->delete();
        return response()->json(['message' => 'Logout realizado com sucesso'])
            ->withCookie(cookie()->forget('access_tokens'))
            ->withCookie(cookie()->forget('refres_tokens'));
    }
}
