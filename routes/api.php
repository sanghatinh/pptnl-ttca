<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;

Route::apiResource('roles', RoleController::class);
Route::apiResource('permissions', PermissionController::class);


Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::get('/logout', [UserController::class, 'logout']);


//ดืงข้อมูลจากตาราง
Route::get('/positions', [UserController::class, 'getPositions']);
Route::get('/stations', [UserController::class, 'getStations']);
//เพิ่มเส้นทางเพื่อดึงข้อมูล roles
Route::get('/roles', [UserController::class, 'getRoles']);
//เพิ่มเส้นทางเพื่อดึงข้อมูล users
Route::get('/users', [UserController::class, 'getUsers']);


Route::post('/user/add', [UserController::class, 'addnewuser']);
Route::put('/user/update/{id}', [UserController::class, 'updateuser']);
Route::get('/user/edit/{id}', [UserController::class, 'edituser']);
Route::get('/user/delete/{id}', [UserController::class, 'deleteuser']);