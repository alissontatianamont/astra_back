<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RoutesModel;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
class RoutesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
            $routes = RoutesModel::where('viaje_estatus', 1)
            ->leftJoin('usuarios', 'viajes.fo_viaje_usuario', '=', 'usuarios.usuario_id')
            ->select('viajes.*', 'usuarios.nombre_usuario  as nombre_conductor')
            ->get();
            $routesArray = $routes->toArray();
            return response()->json($routesArray, 200);
    }

    /**
     * Store a newly created resource in storage.
     */

    
    public function store(Request $request)
    {
        try {
            // Validar los datos
            $validatedData = $request->validate([
                'fo_viaje_usuario' => 'required|integer',
                'viaje_num_manifiesto' => 'required|string',
                'viaje_fecha_manifiesto' => 'required|date',
                'viaje_placa' => 'required|string',
                'viaje_destino_inicio' => 'required|string',
                'viaje_destino_llegada' => 'required|string',
                'viaje_fecha_inicio' => 'required|date',
                'viaje_km_salida' =>'nullable|string', 
                'viaje_km_llegada' =>'nullable|string', 
                'viaje_flete' => 'required|numeric',
                'viaje_anticipo' => 'required|numeric',
                'viaje_neto_pago' => 'required|numeric',
                'viaje_sobrecosto' => 'nullable|numeric',
                'viaje_porcentaje_conductor' => 'required|numeric',
                'viaje_observaciones' => 'nullable|string',
                'viaje_estatus' => 'required|integer',
            ]);
            // Si la validación pasa, guardar los datos en la base de datos
            $fecha_manifiesto = Carbon::createFromFormat('d/m/Y', trim($validatedData['viaje_fecha_manifiesto']))->format('Y-m-d');
            $fecha_inicio = Carbon::createFromFormat('d/m/Y', trim($validatedData['viaje_fecha_inicio']))->format('Y-m-d');
            $file = $request->file("viaje_planilla");
            $originalName = ''; 
            if ($file) {
                $uploadPath = "images/planillas";
                $originalName = "manifiesto_". $validatedData['viaje_num_manifiesto'] . '_' . $validatedData['viaje_placa'] .'_'.$fecha_manifiesto;
                $file->move($uploadPath, $originalName);
            } else {
                $originalName = null;
            }
            // die($originalName);
            $route = new RoutesModel();
            $route->fo_viaje_usuario = $validatedData['fo_viaje_usuario'];
            $route->viaje_num_manifiesto = $validatedData['viaje_num_manifiesto'];
            $route->viaje_fecha_manifiesto = $fecha_manifiesto;
            $route->viaje_placa = $validatedData['viaje_placa'];
            $route->viaje_destino_inicio = $validatedData['viaje_destino_inicio']; 
            $route->viaje_destino_llegada = $validatedData['viaje_destino_llegada'];
            $route->viaje_fecha_inicio = $fecha_inicio;
            $route->viaje_km_salida = $validatedData['viaje_km_salida']; 
            $route->viaje_km_llegada = $validatedData['viaje_km_llegada']; 
            $route->viaje_planilla = $originalName;
            $route->viaje_flete = $validatedData['viaje_flete'];
            $route->viaje_anticipo = $validatedData['viaje_anticipo'];
            $route->viaje_sobrecosto = $validatedData['viaje_sobrecosto'];
            $route->viaje_neto_pago = $validatedData['viaje_neto_pago'];
            $route->viaje_porcentaje_conductor = $validatedData['viaje_porcentaje_conductor'];
            $route->viaje_total_gastos = $validatedData['viaje_porcentaje_conductor']; //depende de una operacion
            $route->viaje_total_ganancias = ($validatedData['viaje_neto_pago'] + $validatedData['viaje_sobrecosto']) - $validatedData['viaje_porcentaje_conductor']; //depende de una operacion
            $route->viaje_estatus = $validatedData['viaje_estatus'];
            $route->viaje_observaciones = $validatedData['viaje_observaciones'];
            $route->save();
    
            // Redirigir o retornar una respuesta
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
    

    /**
     * Display the specified resource.
     */
    public function show( $viaje_id)
    {
        $route =  RoutesModel::find($viaje_id);
        if (!empty($route)) {
            return response()->json($route, 200);
        } else {
            return response()->json([
                'message' => 'registro no encontrado'
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $viaje_id)
    {

        try{
            $validatedData = $request->validate([
                'fo_viaje_usuario' => 'required|integer',
                'viaje_num_manifiesto' => 'required|string',
                'viaje_fecha_manifiesto' => 'required|date',
                'viaje_placa' => 'required|string',
                'viaje_destino_inicio' => 'required|string',
                'viaje_destino_llegada' => 'required|string',
                'viaje_fecha_inicio' => 'required|date',
                'viaje_km_salida' =>'nullable|string', 
                'viaje_km_llegada' =>'nullable|string', 
                'viaje_flete' => 'required|numeric',
                'viaje_anticipo' => 'required|numeric',
                'viaje_neto_pago' => 'required|numeric',
                'viaje_sobrecosto' => 'nullable|numeric',
                'viaje_porcentaje_conductor' => 'required|numeric',
                'viaje_observaciones' => 'nullable|string',
                'viaje_estatus' => 'required|integer',
            ]);
            $route = RoutesModel::find($viaje_id); 
            if ($request->hasFile('viaje_planilla')) {
                $file = $request->file('viaje_planilla');
                $uploadPath = "images/profile";
                $originalName = $request->cedula . '_' . $file->getClientOriginalName();
                $file->move($uploadPath, $originalName);
                $route->viaje_planilla = $originalName;
            }
            $fecha_manifiesto = Carbon::createFromFormat('d/m/Y', trim($validatedData['viaje_fecha_manifiesto']))->format('Y-m-d');
            $fecha_inicio = Carbon::createFromFormat('d/m/Y', trim($validatedData['viaje_fecha_inicio']))->format('Y-m-d');
            $route->fo_viaje_usuario = $validatedData['fo_viaje_usuario'];
            $route->viaje_num_manifiesto = $validatedData['viaje_num_manifiesto'];
            $route->viaje_fecha_manifiesto = $fecha_manifiesto;
            $route->viaje_placa = $validatedData['viaje_placa'];
            $route->viaje_destino_inicio = $validatedData['viaje_destino_inicio']; 
            $route->viaje_destino_llegada = $validatedData['viaje_destino_llegada'];
            $route->viaje_fecha_inicio = $fecha_inicio;
            $route->viaje_km_salida = $validatedData['viaje_km_salida']; 
            $route->viaje_km_llegada = $validatedData['viaje_km_llegada']; 
            $route->viaje_flete = $validatedData['viaje_flete'];
            $route->viaje_anticipo = $validatedData['viaje_anticipo'];
            $route->viaje_sobrecosto = $validatedData['viaje_sobrecosto'];
            $route->viaje_neto_pago = $validatedData['viaje_neto_pago'];
            $route->viaje_porcentaje_conductor = $validatedData['viaje_porcentaje_conductor'];
            $route->viaje_total_gastos = $validatedData['viaje_porcentaje_conductor'];
            $route->viaje_total_ganancias = ($validatedData['viaje_neto_pago'] + $validatedData['viaje_sobrecosto']) - $validatedData['viaje_porcentaje_conductor']; //depende de una operacion
            $route->viaje_estatus = $validatedData['viaje_estatus'];
            $route->viaje_observaciones = $validatedData['viaje_observaciones'];
            $route->save();
            return response()->json([
                'estatus_update' => 1,
                'mensaje' => 'Datos actualizados correctamente. :)'
            ]);
        }catch(ValidationException $e){
            return response()->json([
                'estatus_update' => 0,
                'mensaje' => 'uno o más datos son incorrectos',
                'error' => $e->getMessage()
            ], 422);
        }

        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
