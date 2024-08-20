<?php

use App\Http\Controllers\RoutesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\CarriersController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::post('login', [UsersController::class, 'login'])->name('login');



Route::middleware(['auth:sanctum'])->group(function(){
    // rutas usuarios
    Route::get('images/profile/{filename}', [UsersController::class, 'getProfileImage']);
    Route::get('logout', [UsersController::class,'logout']);
    Route::get('users', [UsersController::class,'index']);
    Route::post('users', [UsersController::class,'store']);
    Route::post('users/{usuario_id}', [UsersController::class,'update']);
    Route::post('delete/{usuario_id}', [UsersController::class,'delete']);
    Route::get('users/{usuario_id}', [UsersController::class,'show']);
    Route::get('get_drivers', [UsersController::class,'getDrivers']);
    //rutas viajes
    Route::get('routes', [RoutesController::class, 'index']);
    Route::post('create_route', [RoutesController::class, 'store']);
    Route::post('update_route/{viaje_id}', [RoutesController::class, 'update']);
    Route::get('get_route/{viaje_id}', [RoutesController::class, 'show']);
    Route::post('delete_route/{viaje_id}', [RoutesController::class, 'delete']);
    //transportadoras
    Route::get('carriers', [CarriersController::class, 'index']);
    Route::post('create_carrier', [CarriersController::class, 'store']);
    Route::get('get_carrier/{carrier_id}', [CarriersController::class, 'show']);
    Route::post('update_carrier/{carrier_id}', [CarriersController::class, 'update']);
    Route::post('delete_carrier/{carrier_id}', [CarriersController::class, 'delete']);

    
});
