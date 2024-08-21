<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExogenousModel;
use Illuminate\Validation\ValidationException;
class ExogenousController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $exogenous = ExogenousModel::where('exogena_estatus', 1)->get();
        $exogenousArray = $exogenous->toArray();
        return response()->json($exogenousArray, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'exogena_nit' => 'required|string',
                'exogena_dv' => 'required|string',
                'exogena_nombre1' => 'nullable|string',
                'exogena_nombre2' => 'nullable|string',
                'exogena_apellido1' => 'nullable|string',
                'exogena_apellido2' => 'nullable|string',
                'exogena_razon_social' => 'nullable|string',
                'exogena_direccion' => 'required|string',
                'exogena_ciudad' => 'required|string',
                'exogena_departamento' => 'required|string',
                'exogena_tipo' => 'required|integer',
                'exogena_estatus'=> 'required|integer',
            ]);
            $exogenous = new ExogenousModel();
            $validatedData['exogena_nombre1'] = ($validatedData['exogena_tipo'] == 2) ? $validatedData['exogena_nombre1'] : null;
            $validatedData['exogena_nombre2'] = ($validatedData['exogena_tipo'] == 2) ? $validatedData['exogena_nombre2'] :  null;
            $validatedData['exogena_apellido1'] = ($validatedData['exogena_tipo'] == 2) ? $validatedData['exogena_apellido1']  : null;
            $validatedData['exogena_apellido2'] = ($validatedData['exogena_tipo'] == 2) ? $validatedData['exogena_apellido2'] : null;
            $validatedData['exogena_razon_social'] = ($validatedData['exogena_tipo'] == 1) ? $validatedData['exogena_razon_social'] : null;
            $exogenous->exogena_nit = $validatedData['exogena_nit'];
            $exogenous->exogena_dv = $validatedData['exogena_dv'];
            $exogenous->exogena_nombre1 = $validatedData['exogena_nombre1'];
            $exogenous->exogena_nombre2 = $validatedData['exogena_nombre2'];
            $exogenous->exogena_apellido1 = $validatedData['exogena_apellido1'];
            $exogenous->exogena_apellido2 = $validatedData['exogena_apellido2'];
            $exogenous->exogena_razon_social = $validatedData['exogena_razon_social'];
            $exogenous->exogena_direccion = $validatedData['exogena_direccion'];
            $exogenous->exogena_ciudad = $validatedData['exogena_ciudad'];
            $exogenous->exogena_departamento = $validatedData['exogena_departamento'];
            $exogenous->exogena_tipo = $validatedData['exogena_tipo'];
            $exogenous->exogena_estatus = $validatedData['exogena_estatus'];
            $exogenous->save();
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
    public function show(string $exogenous_id)
    {
        $exogenous = ExogenousModel::find($exogenous_id);
        if (!empty($exogenous)) {
            return response()->json($exogenous, 200);
        } else {
            return response()->json([
                'message' => 'registro no encontrado'
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $exogenous_id)
    {
        try {
            $validatedData = $request->validate([
                'exogena_nit' => 'required|string',
                'exogena_dv' => 'required|string',
                'exogena_nombre1' => 'nullable|string',
                'exogena_nombre2' => 'nullable|string',
                'exogena_apellido1' => 'nullable|string',
                'exogena_apellido2' => 'nullable|string',
                'exogena_razon_social' => 'nullable|string',
                'exogena_direccion' => 'required|string',
                'exogena_ciudad' => 'required|string',
                'exogena_departamento' => 'required|string',
                'exogena_tipo' => 'required|integer',
                'exogena_estatus'=> 'required|integer',
            ]);
            $validatedData['exogena_nombre1'] = ($validatedData['exogena_tipo'] == 2) ? $validatedData['exogena_nombre1'] : null;
            $validatedData['exogena_nombre2'] = ($validatedData['exogena_tipo'] == 2) ? $validatedData['exogena_nombre2'] :  null;
            $validatedData['exogena_apellido1'] = ($validatedData['exogena_tipo'] == 2) ? $validatedData['exogena_apellido1']  : null;
            $validatedData['exogena_apellido2'] = ($validatedData['exogena_tipo'] == 2) ? $validatedData['exogena_apellido2'] : null;
            $validatedData['exogena_razon_social'] = ($validatedData['exogena_tipo'] == 1) ? $validatedData['exogena_razon_social'] : null;
            $exogenous = ExogenousModel::find($exogenous_id);
            $exogenous->exogena_nit = $validatedData['exogena_nit'];
            $exogenous->exogena_dv = $validatedData['exogena_dv'];
            $exogenous->exogena_nombre1 = $validatedData['exogena_nombre1'];
            $exogenous->exogena_nombre2 = $validatedData['exogena_nombre2'];
            $exogenous->exogena_apellido1 = $validatedData['exogena_apellido1'];
            $exogenous->exogena_apellido2 = $validatedData['exogena_apellido2'];
            $exogenous->exogena_razon_social = $validatedData['exogena_razon_social'];
            $exogenous->exogena_direccion = $validatedData['exogena_direccion'];
            $exogenous->exogena_ciudad = $validatedData['exogena_ciudad'];
            $exogenous->exogena_departamento = $validatedData['exogena_departamento'];
            $exogenous->exogena_tipo = $validatedData['exogena_tipo'];
            $exogenous->exogena_estatus = $validatedData['exogena_estatus'];
            $exogenous->save();
            return response()->json([
                'estatus_guardado' => 1,
                'mensaje' => 'Datos actualizados correctamente. :)'
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
     * Remove the specified resource from storage.
     */
    public function delete($exogenous_id)
    {
        $route = ExogenousModel::find($exogenous_id);
        if ($route) {
            // Actualizar el campo estado_eliminar a 0
            $route->exogena_estatus = 0;
            $route->save();
            
            return response()->json([
                "message" => "Registro eliminado exitosamente."
            ]);
        } else {
            return response()->json([
                "message" => "viaje  no encontrado."
            ], 404);
        }
    }
}
