<?php
declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Auth\Login\AccessToken;
use App\Modules\Auth\Login\GenerateAuthCookies;
use App\Modules\Auth\Login\RefreshToken;
use App\Modules\Auth\Session\VerifyTimeRefresh;
use Illuminate\Http\Request;

class RefreshTokenController extends Controller
{

    public function __construct(private VerifyTimeRefresh $verifyTimeRefresh, private GenerateAuthCookies $generateAuthCookies)
    {

    }


    public function newRefreshToken(Request $request)
    {

        $refreshToken = $request->cookie('refresh_token');
        $this->verifyTimeRefresh->verify($refreshToken);
        $user = User::find($this->verifyTimeRefresh->getSubToken());
        return $this->generateAuthCookies->generate($user);
    }
}

