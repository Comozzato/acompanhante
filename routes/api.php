<?php

use App\Http\Controllers\Auth\loginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ForgotController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\AuthenticateBySessionCookie;

Route::get('/user', function (Request $request) {
    return $request->user();
});


Route::prefix('auth')->group(function () {
    
    Route::post('register', [RegisterController::class, 'register']);
    Route::post('login', [loginController::class, 'login']);
    Route::prefix('password/forgot')->group(function () {
        Route::post('send-code', [ForgotController::class, 'sendCode']);
        Route::post('verify-code', [ForgotController::class, 'verifyCode']);
        Route::post('reset', [ForgotController::class, 'reset']);
    });


    Route::post('/auth/logout', [LoginController::class, 'logout'])->middleware('auth.session');;

});



