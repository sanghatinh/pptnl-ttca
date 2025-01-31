<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('jwt.auth');
Route::get('/me', [AuthController::class, 'me'])->middleware('jwt.auth');

Route::middleware('jwt.auth')->group(function () {
    Route::apiResource('users', UserController::class);
});