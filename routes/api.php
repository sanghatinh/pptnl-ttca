<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\ComponentController;



Route::apiResource('roles', RoleController::class);
Route::apiResource('permissions', PermissionController::class);


Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::get('/logout', [UserController::class, 'logout']);


Route::group(['middleware' => ['auth:api']], function () {
    Route::get('/positions', [UserController::class, 'getPositions']);
    Route::get('/stations', [UserController::class, 'getStations']);
    Route::get('/roles', [UserController::class, 'getRoles']);
    Route::get('/users', [UserController::class, 'getUsers']);
    Route::post('/user/add', [UserController::class, 'addnewuser']);
    Route::put('/user/update/{id}', [UserController::class, 'updateuser']);
    Route::get('/user/edit/{id}', [UserController::class, 'edituser']);
    Route::delete('/user/delete/{id}', [UserController::class, 'deleteuser']);
});


Route::post('/role/permissions', [RolePermissionController::class, 'store']);
Route::get('/roles', [RoleController::class, 'index']);
Route::get('/permissions', [PermissionController::class, 'index']);
Route::get('/components', [ComponentController::class, 'index']);
Route::get('/role/{role}/permissions', [RolePermissionController::class, 'getPermissions']);
Route::get('/role/{role}/components', [RolePermissionController::class, 'getComponents']);