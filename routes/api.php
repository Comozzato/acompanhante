<?php

use App\Http\Controllers\Anunciante\AnuncianteController;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RefreshTokenController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ConviteController;
use App\Http\Controllers\ForgotController;

use App\Models\User;
use Illuminate\Support\Facades\Route;



Route::get('/user', function () {
    return User::find(request()->attributes->get('user_id'));
})->middleware('auth.jwt');

Route::get('role', function () {

    $data = request()->attributes;
    return $data->get('role');
})->middleware('auth.jwt');


Route::get('authenticate', function () {
    return response()->json([
        'message' => Auth::check(),
    ]);
})->middleware('auth.jwt');



Route::prefix('auth')->group(function () {
    Route::prefix('password/forgot')->group(function () {
        Route::post('send-code', [ForgotController::class, 'sendCode']);
        Route::post('verify-code', [ForgotController::class, 'verifyCode']);
        Route::post('reset', [ForgotController::class, 'reset']);
    });
    Route::post('register', [RegisterController::class, 'register']);
    Route::post('login', [LoginController::class, 'login']);
    Route::post('logout', [LogoutController::class, 'logout'])->middleware('auth.jwt');
    Route::post('refresh-token', [RefreshTokenController::class, 'newRefreshToken']);
});




Route::middleware('auth.jwt')->group(function () {
    Route::get('anuciante/{id}', [AnuncianteController::class, 'getDados']);
    Route::post('anuciante/post/{id}', [AnuncianteController::class, 'postDados']);
    Route::post('anuciante/midia/{id}', [AnuncianteController::class, 'postMidia']);
    Route::post('convite', [ConviteController::class, 'enviarConvite']);
    Route::post('anuciante/buscar-anuncios', [AnuncianteController::class, 'getAnuncioCpf']);
    Route::post('anuciante/meus-anuncios', [AnuncianteController::class, 'getMyAnuncios']);
    Route::post('get-imagem', [AnuncianteController::class, 'getImagemFeed']);

});