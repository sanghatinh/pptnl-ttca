<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Print\PrintGiaoNhanHSController;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('{any}', function () {
    return view('app');
})->where('any','.*');

// Route::group(['middleware' => ['auth', 'permission:read']], function () {
//     Route::get('/dashboard', [DashboardController::class, 'index']);
// });


Route::get('/print/giaonhan-hoso/{document_code}', [PrintGiaoNhanHSController::class, 'print'])
    ->name('print.giaonhan-hoso');