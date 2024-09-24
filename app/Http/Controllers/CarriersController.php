<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CarriersModel;
use Illuminate\Validation\ValidationException;

class CarriersController extends Controller
{
    protected $carriersModel;
    public function __construct()
    {
        $this->carriersModel = new CarriersModel();
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->carriersModel->getCarriers();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'transportadora_razon_social' => 'required|string',
                'transportadora_nit' => 'required|string',
                'transportadora_direccion' => 'required|string',
                'transportadora_telefono' => 'required|string',
                'transportadora_dv' => 'required|numeric',
                'transportadora_ciudad' => 'required|string',
                'transportadora_departamento' => 'required|string',
                'transportadora_estatus' => 'required|numeric',
            ]);
            $carrier = $this->carriersModel->saveCarrier($validatedData);
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
    public function show($carrier_id)
    {
        $carrier = $this->carriersModel->getCarrier($carrier_id);
        if (!empty($carrier)) {
            return response()->json($carrier, 200);
        } else {
            return response()->json([
                'message' => 'registro no encontrado'
            ]);
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $carrier_id)
    {
        try {
            // Validar datos
            $validatedData = $request->validate([
                'transportadora_razon_social' => 'required|string',
                'transportadora_nit' => 'required|string',
                'transportadora_direccion' => 'required|string',
                'transportadora_telefono' => 'required|string',
                'transportadora_dv' => 'required|numeric',
                'transportadora_ciudad' => 'required|string',
                'transportadora_departamento' => 'required|string',
                'transportadora_estatus' => 'required|numeric',
            ]);
    
            // Encontrar la transportadora
            $carrier = $this->carriersModel->getCarrier($carrier_id);
    
            // Actualizar la transportadora usando el modelo
            $carrier->updateCarrier($validatedData);
    
            // Retornar respuesta exitosa
            return response()->json([
                'estatus_guardado' => 1,
                'mensaje' => 'Datos actualizados correctamente. :)'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'estatus_guardado' => 0,
                'mensaje' => 'Uno o más datos son incorrectos.',
                'error' => $e->getMessage()
            ], 422);
        }
    }
    

    /**
     * Remove the specified resource from storage.
     */
    public function delete($carrier_id)
    {
        $carrier = $this->carriersModel->getCarrier($carrier_id);
        if ($carrier) {
            // Actualizar el campo estado_eliminar a 0
            $carrier->transportadora_estatus = 0;
            $carrier->save();

            return response()->json([
                "message" => "Registro eliminado con exito"
            ]);
        } else {
            return response()->json([
                "message" => "Registro  no encontrado."
            ], 404);
        }
    }
}
