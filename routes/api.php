<?php

use App\Http\Controllers\Anunciante\AnuncianteController;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RefreshTokenController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ConviteController;
use App\Http\Controllers\ForgotController;
use App\Http\Controllers\Feed\FeedController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
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
    Route::post('anuciante/midia/{id}', action: [AnuncianteController::class, 'postMidia']);
    Route::get('anuciante/meus-anuncios', [AnuncianteController::class, 'getMyAnuncios']);
});

Route::post('get-imagem', [AnuncianteController::class, 'getImagemFeed']);
Route::post('convite', [ConviteController::class, 'enviarConvite']);
Route::post('anuciante/buscar-anuncios', [AnuncianteController::class, 'getAnuncioCpf']);

//Api de Anunciante WordPress
Route::get('anuciante/dados/{id}', [AnuncianteController::class, 'getDados'])->middleware('auth.jwt');
Route::post('anuciante/post/{id}', [AnuncianteController::class, 'postDados'])->middleware('auth.jwt');

// API de Feed
Route::post('post-feed', [FeedController::class, 'post'])->middleware('auth.jwt');
Route::get('posts', [FeedController::class, 'index']);
Route::post('post/aprovar/{id}', [FeedController::class, 'aprovarPublicacao'])->middleware('auth.jwt');
Route::get('posts/user', [FeedController::class, 'indexByUser'])->middleware('auth.jwt');
Route::get('posts/feed/{id}', [FeedController::class, 'findForPostid']);//->middleware('auth.jwt');
Route::get('find/feed/{id}', [FeedController::class, 'findPostById']);
Route::post('imagem', [FeedController::class, 'getImagemFeed']);
Route::delete('delete/feed/{id}', [FeedController::class, 'deleteFeed'])->middleware('auth.jwt');
// API para WordPress buscar
Route::get('wp-json/posts/feed/{tipo}/{id}', [FeedController::class, 'getAllFeedApi'])->middleware('basic.external');
Route::get('wp-json/posts/{tipo}', [FeedController::class, 'getAllFeedApi'])->middleware('basic.external');

// busca as notificacoes do usuario autenticado
Route::get('/notificacoes', function () {
    return auth_user()->notificacoesNaoLidas;
})->middleware('auth.jwt');

// Marcar como lida
Route::post('/notificacoes/{id}/lida', function ($id) {
    $notification = auth_user()->notifications()->findOrFail($id);
    $notification->markAsRead();
    return response()->json(['message' => 'Notificação marcada como lida']);
})->middleware('auth.jwt');


// rotas de teste
Route::get('/rota-protegida-jwt', function () {
    return response()->json(['message' => 'API is working']);
})->middleware('auth.jwt');

Route::get('/rota-protegida-basic', function () {
    return response()->json(['message' => 'API is working']);
})->middleware('basic.external');