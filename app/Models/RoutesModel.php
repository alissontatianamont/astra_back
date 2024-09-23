<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RoutesModel extends Model
{
    use HasFactory;
    protected $table = 'viajes';
    public $timestamps = false;
    protected $primaryKey = 'viaje_id';
    protected $fillable = [
        'fo_viaje_usuario',
        'fo_viaje_transportadora',
        'viaje_destino_inicio',
        'viaje_destino_llegada',
        'viaje_fecha_inicio',
        'viaje_fecha_llegada',
        'viaje_planilla',
        'viaje_total_gastos',
        'viaje_total_ganancias',
        'viaje_estatus'
    ];

    public function getRoutes()
    {
        $routes = RoutesModel::where('viaje_estatus', 1)
            ->leftJoin('usuarios', 'viajes.fo_viaje_usuario', '=', 'usuarios.usuario_id')
            ->select('viajes.*', 'usuarios.nombre_usuario  as nombre_conductor')
            ->get();
        return $routes;
    }
    public function getRoutesByUser($usuario_id)
    {
        $routes = RoutesModel::where('viaje_estatus', 1)
            ->where('fo_viaje_usuario', $usuario_id)
            ->leftJoin('usuarios', 'viajes.fo_viaje_usuario', '=', 'usuarios.usuario_id')
            ->select('viajes.*', 'usuarios.nombre_usuario  as nombre_conductor')
            ->get();
        return $routes;
    }

    public function getRoute($viaje_id)
    {
        $route =  RoutesModel::find($viaje_id);
        return $route;
    }

    public function getDriverName($fo_viaje_usuario)
    {
        $driver = RoutesModel::where('fo_viaje_usuario', $fo_viaje_usuario)
            ->join('usuarios', 'viajes.fo_viaje_usuario', '=', 'usuarios.usuario_id')
            ->select('usuarios.nombre_usuario as viaje_conductor')
            ->first();
        return $driver;
    }

    public function getCountRoutesByMonth()
    {
        $countByMonth = $this->selectRaw('MONTH(viaje_fecha_manifiesto) as month, COUNT(*) as count')
            ->whereYear('viaje_fecha_manifiesto', now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        $result = [];
        for ($i = 1; $i <= 12; $i++) {
            $result[$i] = 0;
        }
        foreach ($countByMonth as $data) {
            $result[$data->month] = $data->count;
        }
        return response()->json($result);
    }
    public function getCountRoutesByMonthUser($userId)
    {
        $countByMonth = $this->selectRaw('MONTH(viaje_fecha_manifiesto) as month, COUNT(*) as count')
            ->whereYear('viaje_fecha_manifiesto', now()->year) // Filtra por el año actual
            ->where('fo_viaje_usuario', $userId) // Filtra por el ID de usuario
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        $result = [];
        for ($i = 1; $i <= 12; $i++) {
            $result[$i] = 0;
        }

        foreach ($countByMonth as $data) {
            $result[$data->month] = $data->count;
        }

        return response()->json($result);
    }


    public function driverPercentage($currentYear)
    {
        $driverPercentages = DB::table('viajes')
            ->select(
                DB::raw('MONTH(viajes.viaje_fecha_manifiesto) as mes'),
                DB::raw('SUM(viajes.viaje_porcentaje_conductor) as total_porcentaje_conductor')
            )
            ->whereYear('viajes.viaje_fecha_manifiesto', $currentYear)
            ->groupBy(DB::raw('MONTH(viajes.viaje_fecha_manifiesto)'))
            ->get();
        return $driverPercentages;
    }


    public function profitsByMonth($currentYear)
    {
        $profitsByMonth = $this->select(
            DB::raw('MONTH(viaje_fecha_manifiesto) as mes'),
            DB::raw('SUM(viaje_total_ganancias) as total_ganancias')
        )
            ->whereYear('viaje_fecha_manifiesto', $currentYear)
            ->groupBy(DB::raw('MONTH(viaje_fecha_manifiesto)'))
            ->get();
        return $profitsByMonth;
    }

    public function profitsByMonthUser($currentYear, $userId)
    {
        $profitsByMonth = $this->select(
            DB::raw('MONTH(viaje_fecha_manifiesto) as mes'),
            DB::raw('SUM(viaje_total_ganancias) as total_ganancias')
        )
            ->whereYear('viaje_fecha_manifiesto', $currentYear)
            ->where('fo_viaje_usuario', $userId) // Filtra por el ID de usuario
            ->groupBy(DB::raw('MONTH(viaje_fecha_manifiesto)'))
            ->get();
        return $profitsByMonth;
    }

    public function saveRoute($validatedData, $originalName, $fo_viaje_transportadora)
    {
        // Convertir las fechas al formato adecuado
        $fecha_manifiesto = Carbon::createFromFormat('d/m/Y', trim($validatedData['viaje_fecha_manifiesto']))->format('Y-m-d');
        $fecha_inicio = Carbon::createFromFormat('d/m/Y', trim($validatedData['viaje_fecha_inicio']))->format('Y-m-d');

        // Asignar los valores al modelo
        $this->fo_viaje_usuario = $validatedData['fo_viaje_usuario'];
        $this->fo_viaje_transportadora = $fo_viaje_transportadora;
        $this->viaje_num_manifiesto = $validatedData['viaje_num_manifiesto'];
        $this->viaje_fecha_manifiesto = $fecha_manifiesto;
        $this->viaje_placa = $validatedData['viaje_placa'];
        $this->viaje_destino_inicio = $validatedData['viaje_destino_inicio'];
        $this->viaje_destino_llegada = $validatedData['viaje_destino_llegada'];
        $this->viaje_fecha_inicio = $fecha_inicio;
        $this->viaje_km_salida = $validatedData['viaje_km_salida'];
        $this->viaje_km_llegada = $validatedData['viaje_km_llegada'];
        $this->viaje_planilla = $originalName;
        $this->viaje_flete = $validatedData['viaje_flete'];
        $this->viaje_anticipo = $validatedData['viaje_anticipo'];
        $this->viaje_sobrecosto = $validatedData['viaje_sobrecosto'];
        $this->viaje_neto_pago = $validatedData['viaje_neto_pago'];
        $this->viaje_porcentaje_conductor = $validatedData['viaje_porcentaje_conductor'];
        $this->viaje_total_gastos = $validatedData['viaje_porcentaje_conductor'];
        $this->viaje_total_ganancias = ($validatedData['viaje_neto_pago'] + $validatedData['viaje_sobrecosto']) - $validatedData['viaje_porcentaje_conductor'];
        $this->viaje_estatus = $validatedData['viaje_estatus'];
        $this->viaje_observaciones = $validatedData['viaje_observaciones'];

        // Guardar el modelo en la base de datos
        $this->save();
    }
    public function updateRoute($validatedData, $originalName = null, $fo_viaje_transportadora)
    {
        // Convertir las fechas al formato adecuado
        $fecha_manifiesto = Carbon::createFromFormat('d/m/Y', trim($validatedData['viaje_fecha_manifiesto']))->format('Y-m-d');
        $fecha_inicio = Carbon::createFromFormat('d/m/Y', trim($validatedData['viaje_fecha_inicio']))->format('Y-m-d');

        // Asignar los valores al modelo
        $this->fo_viaje_usuario = $validatedData['fo_viaje_usuario'];
        $this->fo_viaje_transportadora = $fo_viaje_transportadora;
        $this->viaje_num_manifiesto = $validatedData['viaje_num_manifiesto'];
        $this->viaje_fecha_manifiesto = $fecha_manifiesto;
        $this->viaje_placa = $validatedData['viaje_placa'];
        $this->viaje_destino_inicio = $validatedData['viaje_destino_inicio'];
        $this->viaje_destino_llegada = $validatedData['viaje_destino_llegada'];
        $this->viaje_fecha_inicio = $fecha_inicio;
        $this->viaje_km_salida = $validatedData['viaje_km_salida'];
        $this->viaje_km_llegada = $validatedData['viaje_km_llegada'];
        $this->viaje_flete = $validatedData['viaje_flete'];
        $this->viaje_anticipo = $validatedData['viaje_anticipo'];
        $this->viaje_neto_pago = $validatedData['viaje_neto_pago'];
        $this->viaje_sobrecosto = $validatedData['viaje_sobrecosto'];
        $this->viaje_porcentaje_conductor = $validatedData['viaje_porcentaje_conductor'];
        $this->viaje_total_gastos = $validatedData['viaje_porcentaje_conductor'];
        $this->viaje_total_ganancias = ($validatedData['viaje_neto_pago'] + $validatedData['viaje_sobrecosto']) - $validatedData['viaje_porcentaje_conductor'];
        $this->viaje_estatus = $validatedData['viaje_estatus'];
        $this->viaje_observaciones = $validatedData['viaje_observaciones'];

        // Actualizar la planilla si se ha proporcionado un archivo nuevo
        if ($originalName !== null) {
            $this->viaje_planilla = $originalName;
        }

        // Guardar los cambios en la base de datos
        $this->save();
    }

    public function updateDataRoute($viaje_id, $validatedData)
    {
        $route =  $this->find($viaje_id);
        $route->viaje_total_gastos = $route->viaje_total_gastos + $validatedData['egreso_valor'];
        $route->viaje_total_ganancias = $route->viaje_total_ganancias - $validatedData['egreso_valor'];
        $route->save();
    }

    public function updateRouteTotals($viaje_id, $old_value_egress, $new_value_egress)
    {
        $route = $this->find($viaje_id);

        $route->viaje_total_ganancias = ($route->viaje_total_ganancias + $old_value_egress) - $new_value_egress;
        $route->viaje_total_gastos = ($route->viaje_total_gastos - $old_value_egress) + $new_value_egress;

        $route->save();
    }

    public function updateRouteAfterEgressDeletion($viaje_id, $old_value_egress)
    {
        $route = $this->find($viaje_id);
        if ($route) {
            $route->viaje_total_gastos -= $old_value_egress;
            $route->viaje_total_ganancias += $old_value_egress;
            $route->save();
        }
    }

public function updateRouteAfterEgressItemDeletion($viaje_id, $old_value_egress)
{
    $route = $this->find($viaje_id);
    if ($route) {
        $route->viaje_total_gastos -= $old_value_egress;
        $route->viaje_total_ganancias += $old_value_egress;
        $route->save();
    }
}
public function updateRouteAfterGlobalEgressDeletion($viaje_id, $gasto_valor)
{
    $route = $this->find($viaje_id);
    if ($route) {
        $route->viaje_total_gastos -= $gasto_valor;
        $route->viaje_total_ganancias += $gasto_valor;
        $route->save();
    }
}



}
