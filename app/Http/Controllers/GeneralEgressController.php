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
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request,  $viaje_id)
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
                $single_egress = new EgressModel();
                $single_egress->fo_egreso_viaje = $validatedData['fo_egreso_viaje'];
                $single_egress->egreso_global = $validatedData['egreso_global'];
                $single_egress->egreso_exogenable = $validatedData['egreso_exogenable'];
                $single_egress->fo_egreso_proveedor = $validatedData['fo_egreso_proveedor'];
                $single_egress->egreso_descripcion = $validatedData['egreso_descripcion'];
                $single_egress->egreso_valor = $validatedData['egreso_valor'];
                $single_egress->save();
                //actualizar ganancias y gastos viaje
                $route =  RoutesModel::find($viaje_id);
                $route->viaje_total_gastos = $route->viaje_total_gastos + $validatedData['egreso_valor'];
                $route->viaje_total_ganancias = $route->viaje_total_ganancias - $validatedData['egreso_valor'];
                $route->save();
                return response()->json([
                    'estatus_guardado' => 1,
                    'mensaje' => 'Datos guardados correctamente. :)'
                ]);
            } catch (ValidationException $e) {
                return response()->json([
                    'estatus_guardado' => 0,
                    'mensaje' => 'uno o más datos son incorrectos',
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


                $global_egress = new GlobalEgressModel();
                $global_egress->gasto_g_descripcion = $validatedData['egreso_descripcion'];
                $global_egress->fo_gasto_g_viaje = $validatedData['fo_egreso_viaje'];
                $global_egress->gasto_g_valor = $validatedData['egreso_valor'];
                $global_egress->save();
                $global_egress_id = $global_egress->gasto_g_id;
                if (!empty($validatedData['egressItems'])) {
                    $validatedData_items = json_decode($validatedData['egressItems']);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        foreach ($validatedData_items as $item) {
                            $breakdown_egress = new EgressModel();
                            $breakdown_egress->egreso_exogenable = $item->egreso_exogenable;
                            $breakdown_egress->egreso_global = $request->egreso_global;
                            $breakdown_egress->fo_egreso_viaje = $validatedData['fo_egreso_viaje'];
                            $breakdown_egress->fo_egreso_gasto_global = $global_egress_id;
                            $breakdown_egress->fo_egreso_proveedor = is_numeric($item->fo_egreso_proveedor) ? (int)$item->fo_egreso_proveedor : null;
                            $breakdown_egress->egreso_descripcion = $item->gasto_descripcion;
                            $breakdown_egress->egreso_valor = $item->gasto_valor;
                            $breakdown_egress->save();
                        }
                    } else {
                        echo "Error al decodificar JSON: " . json_last_error_msg();
                    }
                }
                $route =  RoutesModel::find($viaje_id);
                $route->viaje_total_gastos = $route->viaje_total_gastos + $validatedData['egreso_valor'];
                $route->viaje_total_ganancias = $route->viaje_total_ganancias - $validatedData['egreso_valor'];
                $route->save();
                return response()->json([
                    'estatus_guardado' => 1,
                    'mensaje' => 'Datos guardados correctamente. :)'
                ]);
            } catch (ValidationException $e) {
                return response()->json([
                    'estatus_guardado' => 0,
                    'mensaje' => 'uno o más datos son incorrectos',
                    'error' => $e->getMessage()
                ], 422);
            }
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($viaje_id)
    {
        $breakdown_egress = EgressModel::select('egresos.*', 'gastos_globales.gasto_g_id', 'gastos_globales.gasto_g_descripcion', 'gastos_globales.gasto_g_valor')
            ->leftJoin('gastos_globales', 'egresos.fo_egreso_gasto_global', '=', 'gastos_globales.gasto_g_id')->where('fo_egreso_viaje', $viaje_id)
            ->get();
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
        $globalExpenses = GlobalEgressModel::query()
            ->where('fo_gasto_g_viaje', $viaje_id)
            ->get();

        if ($globalExpenses->isEmpty()) {
            return response()->json([
                'message' => 'No se encontraron registros en gastos globales.',
                'status_data' => 0
            ], 200);
        }

        // Obtener el breakdown de egresos con join para cada gasto global
        $breakdown_egress = EgressModel::select('egresos.*', 'gastos_globales.gasto_g_id', 'gastos_globales.gasto_g_descripcion', 'gastos_globales.gasto_g_valor',DB::raw("
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
        $single_egress = EgressModel::where('fo_egreso_viaje', $viaje_id)->where('egreso_global', 0)->get();
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
        $globalExpense = GlobalEgressModel::find($egress_id);

        // Verificar si el egreso global existe
        if (!$globalExpense) {
            return response()->json([
                'message' => 'No se encontró el egreso global con el ID proporcionado.'
            ], 404);
        }

        // Obtener el breakdown de egresos relacionados con este egreso global (subegresos)
        $breakdownEgress = EgressModel::where('fo_egreso_gasto_global', $egress_id)
            ->get();

        // Asignar los subegresos en un subarray 'desglose_gastos'
        $globalExpense->setAttribute('desglose_gastos', $breakdownEgress);

        // Retornar el egreso global junto con los subegresos
        return response()->json($globalExpense, 200);
    }
    public function getOneSingleEgress($egress_id)
    {
        $single_egress = EgressModel::where('egreso_id', $egress_id)
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
    public function updateEgress(Request $request,$viaje_id )
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
                $single_egress = EgressModel::find($request->egreso_id);
                $old_value_egress = $single_egress->egreso_valor;
                $single_egress->fo_egreso_viaje = $validatedData['fo_egreso_viaje'];
                $single_egress->egreso_global = $validatedData['egreso_global'];
                $single_egress->egreso_exogenable = $validatedData['egreso_exogenable'];
                $single_egress->fo_egreso_proveedor = $validatedData['fo_egreso_proveedor'];
                $single_egress->egreso_descripcion = $validatedData['egreso_descripcion'];
                $single_egress->egreso_valor = $validatedData['egreso_valor'];
                $single_egress->save();

                //actualizar ganancias y gastos viaje
                $route =  RoutesModel::find($single_egress->fo_egreso_viaje);
                $route->viaje_total_ganancias = ($route->viaje_total_ganancias + $old_value_egress) - $validatedData['egreso_valor'];
                $route->viaje_total_gastos = ($route->viaje_total_gastos - $old_value_egress) + $validatedData['egreso_valor'];
                $route->save();

                return response()->json([
                    'estatus_guardado' => 1,
                    'mensaje' => 'Datos guardados correctamente. :)'
                ]);
            } catch (ValidationException $e) {
                return response()->json([
                    'estatus_guardado' => 0,
                    'mensaje' => 'uno o más datos son incorrectos',
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

                $global_egress = GlobalEgressModel::find($request->egreso_id);
                $old_value_egress = $global_egress->gasto_g_valor;
                $global_egress->gasto_g_descripcion = $validatedData['egreso_descripcion'];
                $global_egress->fo_gasto_g_viaje = $validatedData['fo_egreso_viaje'];
                $global_egress->gasto_g_valor = $validatedData['egreso_valor'];
                $global_egress->save();
                $global_egress_id = $global_egress->gasto_g_id;
                if (!empty($validatedData['egressItems'])) {
                    $validatedData_items = json_decode($validatedData['egressItems']);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        foreach ($validatedData_items as $item) {
                            (!empty($item->egreso_id)) ? $breakdown_egress = EgressModel::find($item->egreso_id) : $breakdown_egress = new EgressModel();

                            $breakdown_egress->egreso_exogenable = $item->egreso_exogenable;
                            $breakdown_egress->egreso_global = $request->egreso_global;
                            $breakdown_egress->fo_egreso_viaje = $validatedData['fo_egreso_viaje'];
                            $breakdown_egress->fo_egreso_gasto_global = $global_egress_id;
                            $breakdown_egress->fo_egreso_proveedor = is_numeric($item->fo_egreso_proveedor) ? (int)$item->fo_egreso_proveedor : null;
                            $breakdown_egress->egreso_descripcion = $item->gasto_descripcion;
                            $breakdown_egress->egreso_valor = $item->gasto_valor;
                            $breakdown_egress->save();
                        }
                    } else {
                        echo "Error al decodificar JSON: " . json_last_error_msg();
                    }
                }
                $route =  RoutesModel::find($viaje_id);
                $route->viaje_total_ganancias = ($route->viaje_total_ganancias + $old_value_egress) - $validatedData['egreso_valor'];
                $route->viaje_total_gastos = ($route->viaje_total_gastos - $old_value_egress) + $validatedData['egreso_valor'];
                $route->save();
                return response()->json([
                    'estatus_guardado' => 1,
                    'mensaje' => 'Datos guardados correctamente. :)'
                ]);
            } catch (ValidationException $e) {
                return response()->json([
                    'estatus_guardado' => 0,
                    'mensaje' => 'uno o más datos son incorrectos',
                    'error' => $e->getMessage()
                ], 422);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function deleteSingleEgress($viaje_id, $egress_id )
    {
        $single_egress = EgressModel::find($egress_id);
        $old_value_egress = $single_egress->egreso_valor;
        $single_egress->delete();
        $route =  RoutesModel::find($viaje_id);
        $route->viaje_total_gastos = $route->viaje_total_gastos - $old_value_egress;
        $route->viaje_total_ganancias = $route->viaje_total_ganancias + $old_value_egress;
        $route->save();
        return response()->json([
            'estatus_guardado' => 1,
            'mensaje' => 'Datos eliminados correctamente. :)'
        ]);
    }
    public function deleteEgressItem($egress_id)
    {
        $egress = EgressModel::find($egress_id);
        $egress->fo_egreso_gasto_global;
        $global_egress = GlobalEgressModel::find($egress->fo_egreso_gasto_global);
        $old_value_egress = $egress->egreso_valor;
        $global_egress->gasto_g_valor = $global_egress->gasto_g_valor - $old_value_egress;
        $global_egress->save();
        $route =  RoutesModel::find($global_egress->fo_gasto_g_viaje);
        $route->viaje_total_gastos = $route->viaje_total_gastos - $old_value_egress;
        $route->viaje_total_ganancias = $route->viaje_total_ganancias + $old_value_egress;
        $route->save();
        $egress->delete();
        return response()->json([
            'estatus_guardado' => 1,
            'mensaje' => 'Datos eliminados correctamente. :)'
        ],200);
    }
    public function deleteGlobalEgress($egress_id)
    {
        $globalEgress = GlobalEgressModel::find($egress_id);
        if ($globalEgress) {
            $detailEgress = EgressModel::where('fo_egreso_gasto_global', $egress_id)->get();
            if(!$detailEgress->isEmpty()){
                foreach ($detailEgress as  $single_item) {
                    $single_item->delete();
                }
            }
            $route = RoutesModel::find($globalEgress->fo_gasto_g_viaje);
            $route->viaje_total_gastos = $route->viaje_total_gastos - $globalEgress->gasto_g_valor;
            $route->viaje_total_ganancias = $route->viaje_total_ganancias + $globalEgress->gasto_g_valor;
            $route->save();
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
