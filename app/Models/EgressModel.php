<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EgressModel extends Model
{
    use HasFactory;
    protected $table = 'egresos';
    public $timestamps = false;
    protected $primaryKey = 'egreso_id';
    protected $fillable = [
        'fo_egreso_viaje',
        'egreso_global',
        'egreso_exogenable',
        'fo_egreso_gasto_global',
        'fo_egreso_proveedor',
        'egreso_descripcion',
        'egreso_valor'
    ];
    public function filterIndividualEgress($currentYear){
        $individualEgresses = $this->join('viajes', 'viajes.viaje_id', '=', 'egresos.fo_egreso_viaje')
        ->select(
            DB::raw('MONTH(viajes.viaje_fecha_manifiesto) as mes'),
            DB::raw('SUM(egresos.egreso_valor) as total_egreso')
        )
        ->whereNull('fo_egreso_gasto_global')
        ->whereYear('viajes.viaje_fecha_manifiesto', $currentYear)
        ->groupBy(DB::raw('MONTH(viajes.viaje_fecha_manifiesto)'))
        ->get();
        return $individualEgresses;
    }
}
