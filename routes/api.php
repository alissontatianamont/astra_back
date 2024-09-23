<?php

use App\Http\Controllers\RoutesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\CarriersController;
use App\Http\Controllers\ExogenousController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\DashboardInformationController;
use App\Http\Controllers\GeneralEgressController;

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
    Route::post('update_profile/{usuario_id}', [UsersController::class,'updateProfile']);
    Route::post('users/{usuario_id}', [UsersController::class,'update']);
    Route::post('delete/{usuario_id}', [UsersController::class,'delete']);
    Route::get('users/{usuario_id}', [UsersController::class,'show']);
    Route::get('get_drivers', [UsersController::class,'getDrivers']);
    //rutas viajes
    Route::get('routes', [RoutesController::class, 'index']);
    Route::get('get_routes_by_user/{usuario_id}', [RoutesController::class, 'getRoutesByUser']);
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
    // emrpesas info exogena
    Route::get('exogenous', [ExogenousController::class, 'index']);
    Route::get('get_exogenous/{exogenous_id}', [ExogenousController::class, 'show']);
    Route::get('get_exogenous_select', [ExogenousController::class, 'get_exogenous_select']);
    Route::post('update_exogenous/{exogenous_id}', [ExogenousController::class, 'update']);
    Route::post('create_exogenous', [ExogenousController::class, 'store']);
    Route::post('delete_exogenous/{exogenous_id}', [ExogenousController::class, 'delete']);
    // gastos viajes
    Route::post('create_egress/{viaje_id}', [GeneralEgressController::class, 'store']);
    Route::get('get_egress/{viaje_id}', [GeneralEgressController::class, 'show']);
    Route::get('show_or_fetch_global_egress/{viaje_id}', [GeneralEgressController::class, 'showOrFetchGlobalEgress']);
    Route::get('get_single_egress/{viaje_id}', [GeneralEgressController::class, 'getSingleEgress']);
    Route::get('get_one_global_egress/{egress_id}', [GeneralEgressController::class, 'getOneGlobalEgress']);
    Route::get('get_one_single_egress/{egress_id}', [GeneralEgressController::class, 'getOneSingleEgress']);
    Route::post('update_egress/{viaje_id}', [GeneralEgressController::class, 'updateEgress']);
    Route::delete('delete_single_egress/{viaje_id}/{egress_id}', [GeneralEgressController::class, 'deleteSingleEgress']);
    Route::delete('delete_egress_item/{egress_id}', [GeneralEgressController::class, 'deleteEgressItem']);
    Route::delete('delete_global_egress/{egress_id}', [GeneralEgressController::class, 'deleteGlobalEgress']);
    // get file planilla
    Route::get('download_spreadsheet/{filename}', [RoutesController::class, 'downloadSpreadsheet']);
    Route::get('get_driver_name/{fo_viaje_usuario}', [RoutesController::class, 'getDriverName']);
    Route::post('finish_route/{viaje_id}', [RoutesController::class, 'finishRoute']);
    //mthods for dashboard
    Route::get('get_count_routes_by_month', [DashboardInformationController::class, 'getCountRoutesByMonth']);
    Route::get('get_egress_by_month', [DashboardInformationController::class, 'getEgressByMonth']);
    Route::get('get_profits_by_month', [DashboardInformationController::class, 'getProfitsByMonth']);
    Route::get('get_profits_by_month_user/{userId}', [DashboardInformationController::class, 'getProfitsByMonthUser']);
    Route::get('get_egress_by_month_user/{userId}', [DashboardInformationController::class, 'getEgressByMonthUser']);
    Route::get('get_count_routes_by_month_user/{userId}', [DashboardInformationController::class, 'getCountRoutesByMonthUser']);

    //rutas reportes
    Route::get('get_reports_name', [ReportsController::class, 'getReportsName']);
    Route::get('get_report/{rep_id}/{date_start}/{date_end}', [ReportsController::class, 'getReport']);
    Route::get('download_report/{rep_id}/{date_start}/{date_end}', [ReportsController::class, 'downloadReport']);
    Route::get('download_exogenous_report', [ReportsController::class, 'downloadExogenousReport']);
});
