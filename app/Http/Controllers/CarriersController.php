<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CarriersModel;
use Illuminate\Validation\ValidationException;

class CarriersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $carriers = CarriersModel::where('transportadora_estatus', 1)->get();
        $carriersArray = $carriers->toArray();
        return response()->json($carriersArray, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{
            $validatedData = $request->validate([
                'transportadora_razon_social'=>'required|string',
                'transportadora_nit'=>'required|string',
                'transportadora_direccion'=>'required|string',
                'transportadora_telefono'=>'required|string',
                'transportadora_dv'=>'required|numeric',
                'transportadora_ciudad'=>'required|string',
                'transportadora_departamento'=>'required|string',
                'transportadora_estatus'=>'required|numeric',
            ]);
            $carrier = new CarriersModel();
            $carrier->transportadora_razon_social = $validatedData['transportadora_razon_social'];
            $carrier->transportadora_nit = $validatedData['transportadora_nit']; 
            $carrier->transportadora_direccion = $validatedData['transportadora_direccion'];
            $carrier->transportadora_telefono = $validatedData['transportadora_telefono'];
            $carrier->transportadora_dv = $validatedData['transportadora_dv'];
            $carrier->transportadora_ciudad = $validatedData['transportadora_ciudad'];
            $carrier->transportadora_departamento = $validatedData['transportadora_departamento'];
            $carrier->transportadora_estatus = $validatedData['transportadora_estatus'];
            $carrier->save();
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
    public function show( $carrier_id)
    {
        $route =  CarriersModel::find($carrier_id);
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
    public function update(Request $request, string $carrier_id)
    {
        try{
            $validatedData = $request->validate([
                'transportadora_razon_social'=>'required|string',
                'transportadora_nit'=>'required|string',
                'transportadora_direccion'=>'required|string',
                'transportadora_telefono'=>'required|string',
                'transportadora_dv'=>'required|numeric',
                'transportadora_ciudad'=>'required|string',
                'transportadora_departamento'=>'required|string',
                'transportadora_estatus'=>'required|numeric',
            ]);
            $carrier = CarriersModel::find($carrier_id);
            $carrier->transportadora_razon_social = $validatedData['transportadora_razon_social'];
            $carrier->transportadora_nit = $validatedData['transportadora_nit']; 
            $carrier->transportadora_direccion = $validatedData['transportadora_direccion'];
            $carrier->transportadora_telefono = $validatedData['transportadora_telefono'];
            $carrier->transportadora_dv = $validatedData['transportadora_dv'];
            $carrier->transportadora_ciudad = $validatedData['transportadora_ciudad'];
            $carrier->transportadora_departamento = $validatedData['transportadora_departamento'];
            $carrier->transportadora_estatus = $validatedData['transportadora_estatus'];
            $carrier->save();
        // Redirigir o retornar una respuesta
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
    public function delete($carrier_id)
    {
        $carrier = CarriersModel::find($carrier_id);
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
