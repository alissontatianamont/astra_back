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
    public function filerGlobalEgressDashboard($currentYear)
    {
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
    public function saveGlobalEgress($validatedData)
    {
        $global_egress = new GlobalEgressModel();
        $global_egress->gasto_g_descripcion = $validatedData['egreso_descripcion'];
        $global_egress->fo_gasto_g_viaje = $validatedData['fo_egreso_viaje'];
        $global_egress->gasto_g_valor = $validatedData['egreso_valor'];
        $global_egress->save();

        return $global_egress->gasto_g_id;
    }
    public function showOrFetchGlobalEgress($viaje_id)
    {
        $globalExpenses = $this->query()
            ->where('fo_gasto_g_viaje', $viaje_id)
            ->get();
        return $globalExpenses;
    }
    public function getOneGlobalEgress($egress_id)
    {
        $globalExpense = $this->find($egress_id);
        return $globalExpense;
    }
    public function updateGlobalEgress($validatedData, $egreso_id)
    {
        $global_egress = $this->find($egreso_id);
        $old_value_egress = $global_egress->gasto_g_valor;

        $global_egress->gasto_g_descripcion = $validatedData['egreso_descripcion'];
        $global_egress->fo_gasto_g_viaje = $validatedData['fo_egreso_viaje'];
        $global_egress->gasto_g_valor = $validatedData['egreso_valor'];

        $global_egress->save();

        return $old_value_egress;
    }
    public function updateGlobalEgressAfterItemDeletion($global_egress_id, $old_value_egress)
{
    $global_egress = $this->find($global_egress_id);
    if ($global_egress) {
        $global_egress->gasto_g_valor -= $old_value_egress;
        $global_egress->save();
        return $global_egress->fo_gasto_g_viaje;
    }
    return null; 
}
public function deleteGlobalEgressAndItems($egress_id)
{
    $globalEgress = $this->find($egress_id);
    if ($globalEgress) {
        // Obtener los ítems de egreso relacionados
        $detailEgress = EgressModel::where('fo_egreso_gasto_global', $egress_id)->get();
        
        // Eliminar los ítems de egreso
        if (!$detailEgress->isEmpty()) {
            foreach ($detailEgress as $single_item) {
                $single_item->delete();
            }
        }

        return $globalEgress; 
    }
    return null; 
}

}
