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


Route::get('/Danhsachhoso/{id}', function () {
    return view('app');
})->where('id', '[0-9]+');

Route::get('/unauthorized', function () {
    return view('app');
});

Route::get('/print/giaonhan-hoso/{document_code}', [PrintGiaoNhanHSController::class, 'print'])
    ->name('print.giaonhan-hoso');