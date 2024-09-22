<?php

namespace App\Http\Controllers;
use App\Models\RoutesModel;
use App\Models\EgressModel;
use App\Models\GlobalEgressModel;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

class DashboardInformationController extends Controller
{
    public function getCountRoutesByMonth()
    {
        $countByMonth = RoutesModel::selectRaw('MONTH(viaje_fecha_manifiesto) as month, COUNT(*) as count')
            ->whereYear('viaje_fecha_manifiesto', now()->year) // Filtra por el año actual
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    
        // Asegurarte de que todos los meses del último año estén presentes
        $result = [];
        for ($i = 1; $i <= 12; $i++) {
            $result[$i] = 0; // Inicializa todos los meses con 0
        }
    
        foreach ($countByMonth as $data) {
            $result[$data->month] = $data->count; // Asigna el conteo al mes correspondiente
        }
    
        return response()->json($result);
    }
    public function getEgressByMonth()
    {
        $currentYear = date('Y');
    
        // 1. Obtener los egresos globales con la fecha del viaje desde la tabla "viajes" del último año
        $globalEgresses = GlobalEgressModel::join('viajes', 'viajes.viaje_id', '=', 'gastos_globales.fo_gasto_g_viaje')
            ->select(
                DB::raw('MONTH(viajes.viaje_fecha_manifiesto) as mes'),
                DB::raw('SUM(gastos_globales.gasto_g_valor) as total_egreso')
            )
            ->whereYear('viajes.viaje_fecha_manifiesto', $currentYear)
            ->groupBy(DB::raw('MONTH(viajes.viaje_fecha_manifiesto)'))
            ->get();
    
        // 2. Obtener los egresos individuales sin fo_egreso_gasto_global con la fecha del viaje desde la tabla "viajes" del último año
        $individualEgresses = EgressModel::join('viajes', 'viajes.viaje_id', '=', 'egresos.fo_egreso_viaje')
            ->select(
                DB::raw('MONTH(viajes.viaje_fecha_manifiesto) as mes'),
                DB::raw('SUM(egresos.egreso_valor) as total_egreso')
            )
            ->whereNull('fo_egreso_gasto_global')
            ->whereYear('viajes.viaje_fecha_manifiesto', $currentYear)
            ->groupBy(DB::raw('MONTH(viajes.viaje_fecha_manifiesto)'))
            ->get();
    
        // 3. Obtener el porcentaje del conductor por mes desde la tabla "viajes" del último año
        $driverPercentages = DB::table('viajes')
            ->select(
                DB::raw('MONTH(viajes.viaje_fecha_manifiesto) as mes'),
                DB::raw('SUM(viajes.viaje_porcentaje_conductor) as total_porcentaje_conductor')
            )
            ->whereYear('viajes.viaje_fecha_manifiesto', $currentYear)
            ->groupBy(DB::raw('MONTH(viajes.viaje_fecha_manifiesto)'))
            ->get();
    
        // 4. Inicializar un array con los meses del año en 0
        $combinedEgresses = collect(range(1, 12))->mapWithKeys(function ($month) {
            return [$month => 0];
        });
    
        // 5. Combinar los egresos globales e individuales y sumar por mes
        $globalEgresses->concat($individualEgresses)
            ->groupBy('mes')
            ->each(function ($monthEgresses, $mes) use (&$combinedEgresses) {
                $combinedEgresses[$mes] += $monthEgresses->sum('total_egreso');
            });
    
        // 6. Sumar los porcentajes del conductor a los egresos combinados
        foreach ($driverPercentages as $percentage) {
            $combinedEgresses[$percentage->mes] += $percentage->total_porcentaje_conductor;
        }
    
        return response()->json($combinedEgresses);
    }
    public function getProfitsByMonth()
    {
        $currentYear = now()->year;
    
        // Obtener las ganancias de la tabla "viajes" del último año, agrupadas por mes
        $profitsByMonth = RoutesModel::select(
            DB::raw('MONTH(viaje_fecha_manifiesto) as mes'),
            DB::raw('SUM(viaje_total_ganancias) as total_ganancias')
        )
        ->whereYear('viaje_fecha_manifiesto', $currentYear)
        ->groupBy(DB::raw('MONTH(viaje_fecha_manifiesto)'))
        ->get();
    
        // Si hay meses vacíos, rellenarlos con 0
        $profitsWithZeros = collect(range(1, 12))->mapWithKeys(function ($month) use ($profitsByMonth) {
            $profit = $profitsByMonth->firstWhere('mes', $month);
            return [$month => $profit ? $profit->total_ganancias : 0];
        });
    
        return response()->json($profitsWithZeros);
    }
    
    
    
    
    
    
    
}
