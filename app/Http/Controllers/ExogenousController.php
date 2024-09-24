<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExogenousModel;
use Illuminate\Validation\ValidationException;
class ExogenousController extends Controller
{
    protected $exogenousModel;
    public function __construct() {
        $this->exogenousModel = new ExogenousModel();
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->exogenousModel->getExogenous();
    }
    public function get_exogenous_select()
    {
            return $this->exogenousModel->getExogenousSelect();
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
    
            // Ajustes según tipo de exógena
            $validatedData['exogena_nombre1'] = ($validatedData['exogena_tipo'] == 2) ? $validatedData['exogena_nombre1'] : '';
            $validatedData['exogena_nombre2'] = ($validatedData['exogena_tipo'] == 2) ? $validatedData['exogena_nombre2'] :  '';
            $validatedData['exogena_apellido1'] = ($validatedData['exogena_tipo'] == 2) ? $validatedData['exogena_apellido1']  : '';
            $validatedData['exogena_apellido2'] = ($validatedData['exogena_tipo'] == 2) ? $validatedData['exogena_apellido2'] : '';
            $validatedData['exogena_razon_social'] = ($validatedData['exogena_tipo'] == 1) ? $validatedData['exogena_razon_social'] : '';
    
            // Mover lógica de guardado al modelo
            $this->exogenousModel->saveExogenous($validatedData);
    
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
    
            // Ajustes según tipo de exógena
            $validatedData['exogena_nombre1'] = ($validatedData['exogena_tipo'] == 2) ? $validatedData['exogena_nombre1'] : '';
            $validatedData['exogena_nombre2'] = ($validatedData['exogena_tipo'] == 2) ? $validatedData['exogena_nombre2'] :  '';
            $validatedData['exogena_apellido1'] = ($validatedData['exogena_tipo'] == 2) ? $validatedData['exogena_apellido1']  : '';
            $validatedData['exogena_apellido2'] = ($validatedData['exogena_tipo'] == 2) ? $validatedData['exogena_apellido2'] : '';
            $validatedData['exogena_razon_social'] = ($validatedData['exogena_tipo'] == 1) ? $validatedData['exogena_razon_social'] : '';
    
            // Mover lógica de actualización al modelo
            $this->exogenousModel->updateExogenous($exogenous_id, $validatedData);
    
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
        $exogenous = $this->exogenousModel->getExogenousById($exogenous_id);
        if ($exogenous) {
            // Actualizar el campo estado_eliminar a 0
            $exogenous->exogena_estatus = 0;
            $exogenous->save();
            
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
