<?php

use App\Http\Controllers\Auth\LogoutController;
use Illuminate\Support\Facades\Route;



Route::post('/logout', [LogoutController::class, 'logout'])->middleware('auth:sanctum');