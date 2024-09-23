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

    public function saveEgress($validatedData){
        $this->fo_egreso_viaje = $validatedData['fo_egreso_viaje'];
        $this->egreso_global = $validatedData['egreso_global'];
        $this->egreso_exogenable = $validatedData['egreso_exogenable'];
        $this->fo_egreso_proveedor = $validatedData['fo_egreso_proveedor'];
        $this->egreso_descripcion = $validatedData['egreso_descripcion'];
        $this->egreso_valor = $validatedData['egreso_valor'];
        $this->save();
    }
    public function saveBreakdownEgress($validatedData_items, $validatedData, $global_egress_id)
    {
        foreach ($validatedData_items as $item) {
            $this->egreso_exogenable = $item->egreso_exogenable;
            $this->egreso_global = $validatedData['egreso_global'];
            $this->fo_egreso_viaje = $validatedData['fo_egreso_viaje'];
            $this->fo_egreso_gasto_global = $global_egress_id;
            $this->fo_egreso_proveedor = is_numeric($item->fo_egreso_proveedor) ? (int)$item->fo_egreso_proveedor : null;
            $this->egreso_descripcion = $item->gasto_descripcion;
            $this->egreso_valor = $item->gasto_valor;
            $this->save();
        }
    }

    public function showBreakdownEgress($viaje_id){
        $breakdown_egress = $this->select('egresos.*', 'gastos_globales.gasto_g_id', 'gastos_globales.gasto_g_descripcion', 'gastos_globales.gasto_g_valor')
            ->leftJoin('gastos_globales', 'egresos.fo_egreso_gasto_global', '=', 'gastos_globales.gasto_g_id')->where('fo_egreso_viaje', $viaje_id)
            ->get();
            return $breakdown_egress;
    }


    public function get_breakdown_egress($viaje_id){
        $breakdown_egress = $this->select('egresos.*', 'gastos_globales.gasto_g_id', 'gastos_globales.gasto_g_descripcion', 'gastos_globales.gasto_g_valor',DB::raw("
        CASE 
            WHEN exogenas.exogena_tipo = 2 THEN 
                CONCAT(IFNULL(exogenas.exogena_nombre1, ''), ' ', IFNULL(exogenas.exogena_nombre2, ''), ' ', IFNULL(exogenas.exogena_apellido1, ''), ' ', IFNULL(exogenas.exogena_apellido2, ''))
            ELSE 
                exogenas.exogena_razon_social
        END as nombre_razon_social
    ")
    )
            ->leftJoin('gastos_globales', 'egresos.fo_egreso_gasto_global', '=', 'gastos_globales.gasto_g_id')->leftjoin('exogenas', 'egresos.fo_egreso_proveedor', '=', 'exogenas.exogena_id')
            ->where('fo_egreso_viaje', $viaje_id)
            ->get();
            return $breakdown_egress;
    }

    public function breakdownEgress($egress_id){
        $breakdownEgress = EgressModel::where('fo_egreso_gasto_global', $egress_id)
            ->get();
            return $breakdownEgress;
    }

    public function updateSingleEgress($validatedData, $egreso_id)
{
    $single_egress = $this->find($egreso_id);
    $old_value_egress = $single_egress->egreso_valor;
    
    $single_egress->fo_egreso_viaje = $validatedData['fo_egreso_viaje'];
    $single_egress->egreso_global = $validatedData['egreso_global'];
    $single_egress->egreso_exogenable = $validatedData['egreso_exogenable'];
    $single_egress->fo_egreso_proveedor = $validatedData['fo_egreso_proveedor'];
    $single_egress->egreso_descripcion = $validatedData['egreso_descripcion'];
    $single_egress->egreso_valor = $validatedData['egreso_valor'];
    
    $single_egress->save();

    return $old_value_egress;
}
public function updateOrCreateBreakdownEgress($validatedData, $validatedData_items, $egreso_global, $global_egress_id)
{
    foreach ($validatedData_items as $item) {
        $breakdown_egress = !empty($item->egreso_id) ? self::find($item->egreso_id) : new self();

        $breakdown_egress->egreso_exogenable = $item->egreso_exogenable;
        $breakdown_egress->egreso_global = $egreso_global;
        $breakdown_egress->fo_egreso_viaje = $validatedData['fo_egreso_viaje'];
        $breakdown_egress->fo_egreso_gasto_global = $global_egress_id;
        $breakdown_egress->fo_egreso_proveedor = is_numeric($item->fo_egreso_proveedor) ? (int)$item->fo_egreso_proveedor : null;
        $breakdown_egress->egreso_descripcion = $item->gasto_descripcion;
        $breakdown_egress->egreso_valor = $item->gasto_valor;
        
        $breakdown_egress->save();
    }
}
public function deleteEgress($egress_id)
{
    $single_egress = $this->find($egress_id);
    if ($single_egress) {
        $old_value_egress = $single_egress->egreso_valor;
        $single_egress->delete();
        return $old_value_egress;
    }
    return null; 
}
public function deleteEgressItem($egress_id)
{
    $egress = $this->find($egress_id);
    if ($egress) {
        $old_value_egress = $egress->egreso_valor;
        $global_egress_id = $egress->fo_egreso_gasto_global;
        $egress->delete();
        return [
            'old_value_egress' => $old_value_egress,
            'global_egress_id' => $global_egress_id
        ];
    }
    return null; // Retornar null si no se encuentra el egreso
}


}
