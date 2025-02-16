<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('{any}', function () {
    return view('app');
})->where('any','.*');

// Route::group(['middleware' => ['auth', 'permission:read']], function () {
//     Route::get('/dashboard', [DashboardController::class, 'index']);
// });