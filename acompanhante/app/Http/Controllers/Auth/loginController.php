<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

use App\Models\SessionModel;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Str;

class loginController extends Controller
{
    //
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!Auth::attempt($credentials)) {
            throw new InvalidArgumentException('Credenciais inválidas', 401);
        }

        $user = Auth::user();

        $session = SessionModel::create([
            'user_id' => $user->id, // Certifique-se de que $user->id já é uma string
            'access_token' => Str::random(60),
            'refresh_token' => Str::random(80),
            'expires_at' => now()->addHours(2),
        ]);

        return response()->json([
            'id_session' => $session->id_session,
            'access_token' => $session->access_token,
            'refresh_token' => $session->refresh_token,
            'expires_at' => $session->expires_at,
        ]);
    }
}
