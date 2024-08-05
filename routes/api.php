<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsersController;

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
    Route::get('images/profile/{filename}', [UsersController::class, 'getProfileImage']);
    Route::get('logout', [UsersController::class,'logout']);
    Route::get('users', [UsersController::class,'index']);
    Route::post('users', [UsersController::class,'store']);



Route::post('users/{usuario_id}', [UsersController::class,'update']);
Route::post('delete/{usuario_id}', [UsersController::class,'delete']);


Route::get('users/{usuario_id}', [UsersController::class,'show']);
});
