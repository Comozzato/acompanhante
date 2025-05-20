<?php

use App\Http\Controllers\Auth\loginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ForgotController;
use App\Modules\Anunciante\Controllers\AnuncianteController;
use App\Services\AnuncioApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::get('/user', function () {
    return 'logado';
})->middleware('auth.session');

Route::get('role', function () {
    return Auth::user()->role;
})->middleware('auth.session');

Route::get('authenticate', function () {
    return response()->json([
        'message' => Auth::check(),
    ]);
})->middleware('auth.session');
;


Route::prefix('auth')->group(function () {
    Route::prefix('password/forgot')->group(function () {
        Route::post('send-code', [ForgotController::class, 'sendCode']);
        Route::post('verify-code', [ForgotController::class, 'verifyCode']);
        Route::post('reset', [ForgotController::class, 'reset']);
    });


    Route::post('register', [RegisterController::class, 'register']);
    Route::post('login', [loginController::class, 'login']);
    Route::post('logout', [LogoutController::class, 'logout'])->middleware('auth.jwt');
});



Route::get('anuciante/{id}', [AnuncianteController::class, 'getDados']);

Route::post('anuciante/post/{id}', [AnuncianteController::class, 'postDados']);
Route::post('anuciante/midia/{id}', [AnuncianteController::class, 'postMidia']);


