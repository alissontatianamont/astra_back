<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EgressModel;
use App\Models\GlobalEgressModel;
use App\Models\RoutesModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GeneralEgressController extends Controller
{

    protected $egressModel;
    protected $globalEgressModel;
    protected $routesModel;


    public function __construct() {
        $this->egressModel = new EgressModel();
        $this->globalEgressModel = new GlobalEgressModel(); 
        $this->routesModel = new RoutesModel();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $viaje_id)
    {
        try {
            if ($request->egreso_global == 0) {
                // Validación de egresos individuales
                $validatedData = $request->validate([
                    'fo_egreso_viaje' => 'required|numeric',
                    'egreso_global' => 'required|string',
                    'egreso_exogenable' => 'required|numeric',
                    'fo_egreso_proveedor' => 'nullable|numeric',
                    'egreso_descripcion' => 'nullable|string',
                    'egreso_valor' => 'required|numeric',
                ]);
    
                // Guardar egreso individual
                $this->egressModel->saveEgress($validatedData);
    
                // Actualizar ruta
                $this->routesModel->updateDataRoute($viaje_id, $validatedData);
    
            } elseif ($request->egreso_global == 1) {
                // Validación de egresos globales
                $validatedData = $request->validate([
                    'fo_egreso_viaje' => 'required|numeric',
                    'egreso_global' => 'required|string',
                    'egreso_descripcion' => 'nullable|string',
                    'fo_egreso_proveedor' => 'nullable|numeric',
                    'egreso_valor' => 'required|numeric',
                    'egressItems' => 'nullable|string',
                ]);
    
                // Guardar egreso global
                $global_egress_id = $this->globalEgressModel->saveGlobalEgress($validatedData);
    
                // Guardar desglose de egresos si existen
                if (!empty($validatedData['egressItems'])) {
                    $validatedData_items = json_decode($validatedData['egressItems']);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $this->egressModel->saveBreakdownEgress($validatedData_items, $validatedData, $global_egress_id);
                    } else {
                        echo "Error al decodificar JSON: " . json_last_error_msg();
                    }
                }
    
                // Actualizar ruta
                $this->routesModel->updateDataRoute($viaje_id, $validatedData);
            }
    
            return response()->json([
                'estatus_guardado' => 1,
                'mensaje' => 'Datos guardados correctamente. :)'
            ]);
    
        } catch (ValidationException $e) {
            return response()->json([
                'estatus_guardado' => 0,
                'mensaje' => 'Uno o más datos son incorrectos',
                'error' => $e->getMessage()
            ], 422);
        }
    }
    

    /**
     * Display the specified resource.
     */
    public function show($viaje_id)
    {
        $breakdown_egress = $this->egressModel->showBreakdownEgress($viaje_id);
        if (!empty($breakdown_egress)) {
            return response()->json($breakdown_egress, 200);
        } else {
            return response()->json([
                'message' => 'registro(s) no encontrado'
            ]);
        }
    }

    public function showOrFetchGlobalEgress($viaje_id)
    {
        // Obtener todos los registros de la tabla 'gastos_globales'
        $globalExpenses = $this->globalEgressModel->showOrFetchGlobalEgress($viaje_id);

        if ($globalExpenses->isEmpty()) {
            return response()->json([
                'message' => 'No se encontraron registros en gastos globales.',
                'status_data' => 0
            ], 200);
        }
        // Obtener el breakdown de egresos con join para cada gasto global
        $breakdown_egress = $this->egressModel->get_breakdown_egress($viaje_id);

        // Agrupar los egresos por fo_egreso_gasto_global (gasto_g_id)
        $groupedEgress = $breakdown_egress->groupBy('fo_egreso_gasto_global');

        // Combinar los gastos globales con los egresos agrupados en un subarray
        $combinedResults = $globalExpenses->map(function ($globalExpense) use ($groupedEgress) {
            $gasto_id = $globalExpense->gasto_g_id;

            // Agregar los egresos correspondientes al gasto global en un subarray 'desglose_gastos'
            $globalExpense->setAttribute('desglose_gastos', $groupedEgress->has($gasto_id) ? $groupedEgress[$gasto_id] : []);

            return $globalExpense;
        });

        return response()->json($combinedResults, 200);
    }


    public function getSingleEgress($viaje_id)
    {
        $single_egress = $this->egressModel->where('fo_egreso_viaje', $viaje_id)->where('egreso_global', 0)->get();
        if (!empty($single_egress)) {
            return response()->json($single_egress, 200);
        } else {
            return response()->json([
                'message' => 'registro(s) no encontrado'
            ]);
        }
    }

    public function getOneGlobalEgress($egress_id)
    {
        // Obtener el egreso global por el ID proporcionado
        $globalExpense = $this->globalEgressModel->getOneGlobalEgress($egress_id);

        // Verificar si el egreso global existe
        if (!$globalExpense) {
            return response()->json([
                'message' => 'No se encontró el egreso global con el ID proporcionado.'
            ], 404);
        }

        // Obtener el breakdown de egresos relacionados con este egreso global (subegresos)
        $breakdownEgress = $this->egressModel->breakdownEgress($egress_id);

        // Asignar los subegresos en un subarray 'desglose_gastos'
        $globalExpense->setAttribute('desglose_gastos', $breakdownEgress);

        // Retornar el egreso global junto con los subegresos
        return response()->json($globalExpense, 200);
    }


    public function getOneSingleEgress($egress_id)
    {
        $single_egress = $this->egressModel->where('egreso_id', $egress_id)
            ->where('egreso_global', 0)
            ->first();

        // Verificar si se encontró el egreso
        if ($single_egress) {
            return response()->json($single_egress, 200);
        } else {
            return response()->json([
                'message' => 'registro(s) no encontrado'
            ], 404);
        }
    }



    /**
     * Update the specified resource in storage.
     */
    public function updateEgress(Request $request, $viaje_id)
    {
        if ($request->egreso_global == 0) {
            try {
                $validatedData = $request->validate([
                    'fo_egreso_viaje' => 'required|numeric',
                    'egreso_global' => 'required|string',
                    'egreso_exogenable' => 'required|numeric',
                    'fo_egreso_proveedor' => 'nullable|numeric',
                    'egreso_descripcion' => 'nullable|string',
                    'egreso_valor' => 'required|numeric',
                ]);
    
                $old_value_egress = $this->egressModel->updateSingleEgress($validatedData, $request->egreso_id);
                $this->routesModel->updateRouteTotals($viaje_id, $old_value_egress, $validatedData['egreso_valor']);
    
                return response()->json([
                    'estatus_guardado' => 1,
                    'mensaje' => 'Datos guardados correctamente. :)'
                ]);
            } catch (ValidationException $e) {
                return response()->json([
                    'estatus_guardado' => 0,
                    'mensaje' => 'Uno o más datos son incorrectos',
                    'error' => $e->getMessage()
                ], 422);
            }
        } elseif ($request->egreso_global == 1) {
            try {
                $validatedData = $request->validate([
                    'fo_egreso_viaje' => 'required|numeric',
                    'egreso_global' => 'required|string',
                    'egreso_descripcion' => 'nullable|string',
                    'fo_egreso_proveedor' => 'nullable|numeric',
                    'egreso_valor' => 'required|numeric',
                    'egressItems' => 'nullable|string'
                ]);
    
                $old_value_egress = $this->globalEgressModel->updateGlobalEgress($validatedData, $request->egreso_id);
    
                if (!empty($validatedData['egressItems'])) {
                    $validatedData_items = json_decode($validatedData['egressItems']);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $this->egressModel->updateOrCreateBreakdownEgress($validatedData, $validatedData_items, $request->egreso_global, $request->egreso_id);
                    } else {
                        echo "Error al decodificar JSON: " . json_last_error_msg();
                    }
                }
    
                $this->routesModel->updateRouteTotals($viaje_id, $old_value_egress, $validatedData['egreso_valor']);
    
                return response()->json([
                    'estatus_guardado' => 1,
                    'mensaje' => 'Datos guardados correctamente. :)'
                ]);
            } catch (ValidationException $e) {
                return response()->json([
                    'estatus_guardado' => 0,
                    'mensaje' => 'Uno o más datos son incorrectos',
                    'error' => $e->getMessage()
                ], 422);
            }
        }
    }
    

    /**
     * Remove the specified resource from storage.
     */
    public function deleteSingleEgress($viaje_id, $egress_id)
    {
        // Eliminar el egreso y obtener el valor del egreso eliminado
        $old_value_egress = $this->egressModel->deleteEgress($egress_id);
        
        if ($old_value_egress !== null) {
            // Actualizar la ruta después de eliminar el egreso
            $this->routesModel->updateRouteAfterEgressDeletion($viaje_id, $old_value_egress);
            
            return response()->json([
                'estatus_guardado' => 1,
                'mensaje' => 'Datos eliminados correctamente. :)'
            ]);
        } else {
            return response()->json([
                'estatus_guardado' => 0,
                'mensaje' => 'No se encontró el egreso.'
            ], 404);
        }
    }
    


    public function deleteEgressItem($egress_id)
{
    $result = $this->egressModel->deleteEgressItem($egress_id);
    
    if ($result !== null) {
        $old_value_egress = $result['old_value_egress'];
        $global_egress_id = $result['global_egress_id'];
        
        $viaje_id = $this->globalEgressModel->updateGlobalEgressAfterItemDeletion($global_egress_id, $old_value_egress);
        
        if ($viaje_id !== null) {
            $this->routesModel->updateRouteAfterEgressItemDeletion($viaje_id, $old_value_egress);
        }
        
        return response()->json([
            'estatus_guardado' => 1,
            'mensaje' => 'Datos eliminados correctamente. :)'
        ], 200);
    } else {
        return response()->json([
            'estatus_guardado' => 0,
            'mensaje' => 'No se encontró el ítem de egreso.'
        ], 404);
    }
}



public function deleteGlobalEgress($egress_id)
{
    // Eliminar el egreso global y sus ítems
    $globalEgress = $this->globalEgressModel->deleteGlobalEgressAndItems($egress_id);
    
    if ($globalEgress !== null) {
        // Actualizar la ruta después de eliminar el egreso global
        $route_id = $globalEgress->fo_gasto_g_viaje;
        $gasto_valor = $globalEgress->gasto_g_valor;
        $this->routesModel->updateRouteAfterGlobalEgressDeletion($route_id, $gasto_valor);
        
        // Eliminar el egreso global
        $globalEgress->delete();
        
        return response()->json([
            "message" => "Gasto eliminado correctamente."
        ]);
    } else {
        return response()->json([
            "message" => "Gasto no encontrado."
        ], 404);
    }
}

}
