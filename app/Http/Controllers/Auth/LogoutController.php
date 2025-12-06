<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

use App\Modules\Auth\Logout\DeleteCookies;
use Auth;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    //
    public function __construct(private DeleteCookies $deleteCookies)
    {

    }
    public function logout(Request $request)
    {
        return $this->deleteCookies->logout($request);
    }
}
