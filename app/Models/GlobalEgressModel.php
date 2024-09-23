<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class GlobalEgressModel extends Model
{
    use HasFactory;
    protected $table = 'gastos_globales';
    public $timestamps = false;
    protected $primaryKey = 'gasto_g_id';
    protected $fillable = [
        'gasto_g_descripcion',
        'fo_gasto_g_viaje',
        'gasto_g_valor',
    ];
    public function filerGlobalEgressDashboard($currentYear){
     // 1. Obtener los egresos globales con la fecha del viaje desde la tabla "viajes" del último año
     $globalEgresses = $this->join('viajes', 'viajes.viaje_id', '=', 'gastos_globales.fo_gasto_g_viaje')
     ->select(
         DB::raw('MONTH(viajes.viaje_fecha_manifiesto) as mes'),
         DB::raw('SUM(gastos_globales.gasto_g_valor) as total_egreso')
     )
     ->whereYear('viajes.viaje_fecha_manifiesto', $currentYear)
     ->groupBy(DB::raw('MONTH(viajes.viaje_fecha_manifiesto)'))
     ->get();
     return $globalEgresses;
    }
}
