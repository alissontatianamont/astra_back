<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RoutesModel;
use App\Models\EgressModel;
use App\Models\GlobalEgressModel;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;

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
                 'viaje_fecha_manifiesto' => 'required|string',
                 'viaje_placa' => 'required|string',
                 'viaje_destino_inicio' => 'required|string',
                 'viaje_destino_llegada' => 'required|string',
                 'viaje_fecha_inicio' => 'required|string',
                 'viaje_km_salida' => 'nullable|string',
                 'viaje_km_llegada' => 'nullable|string',
                 'viaje_flete' => 'required|numeric',
                 'viaje_anticipo' => 'required|numeric',
                 'viaje_neto_pago' => 'required|numeric',
                 'viaje_sobrecosto' => 'nullable|numeric',
                 'viaje_porcentaje_conductor' => 'required|numeric',
                 'viaje_observaciones' => 'nullable|string',
                 'viaje_estatus' => 'required|integer',
             ]);
     
             // Convertir fechas al formato adecuado
             $fecha_manifiesto = Carbon::createFromFormat('d/m/Y', trim($validatedData['viaje_fecha_manifiesto']))->format('Y-m-d');
             $fecha_inicio = Carbon::createFromFormat('d/m/Y', trim($validatedData['viaje_fecha_inicio']))->format('Y-m-d');
     
             $file = $request->file("viaje_planilla");
             $originalName = '';
     
             if ($file) {
                 // Crear el nombre base del archivo
                 $baseName = "manifiesto_" . $validatedData['viaje_num_manifiesto'] . '_' . $validatedData['viaje_placa'] . '_' . $fecha_manifiesto;
     
                 // Obtener la extensión del archivo
                 $extension = $file->guessExtension();
                 $originalName = $baseName . '.' . $extension;
     
                 // Mover el archivo a la carpeta "spreadsheets"
                 $uploadPath = "spreadsheets"; // Directorio para guardar archivos
                 $file->move($uploadPath, $originalName);
             } else {
                 $originalName = null;
             }
     
             // Guardar los datos en la base de datos
             $route = new RoutesModel();
             $route->fo_viaje_usuario = $validatedData['fo_viaje_usuario'];
             $route->fo_viaje_transportadora = $request->fo_viaje_transportadora;
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
             $route->viaje_total_gastos = $validatedData['viaje_porcentaje_conductor']; // depende de una operación
             $route->viaje_total_ganancias = ($validatedData['viaje_neto_pago'] + $validatedData['viaje_sobrecosto']) - $validatedData['viaje_porcentaje_conductor'];
             $route->viaje_estatus = $validatedData['viaje_estatus'];
             $route->viaje_observaciones = $validatedData['viaje_observaciones'];
             $route->save();
     
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
        try {
            // Validar los datos
            $validatedData = $request->validate([
                'fo_viaje_usuario' => 'required|integer',
                'viaje_num_manifiesto' => 'required|string',
                'viaje_fecha_manifiesto' => 'required|string',
                'viaje_placa' => 'required|string',
                'viaje_destino_inicio' => 'required|string',
                'viaje_destino_llegada' => 'required|string',
                'viaje_fecha_inicio' => 'required|string',
                'viaje_km_salida' => 'nullable|string',
                'viaje_km_llegada' => 'nullable|string',
                'viaje_flete' => 'required|numeric',
                'viaje_anticipo' => 'required|numeric',
                'viaje_neto_pago' => 'required|numeric',
                'viaje_sobrecosto' => 'nullable|numeric',
                'viaje_porcentaje_conductor' => 'required|numeric',
                'viaje_observaciones' => 'nullable|string',
                'viaje_estatus' => 'required|integer',
            ]);
    
            // Encontrar el registro
            $route = RoutesModel::find($viaje_id);
            if (!$route) {
                return response()->json([
                    'estatus_update' => 0,
                    'mensaje' => 'El viaje no fue encontrado',
                ], 404);
            }
    
            // Verificar si se ha subido un nuevo archivo
            if ($request->hasFile('viaje_planilla')) {
                $file = $request->file('viaje_planilla');
    
                // Obtener el tipo de archivo
                $mimeType = $file->getClientMimeType();
                $baseName = "manifiesto_" . $validatedData['viaje_num_manifiesto'] . '_' . $validatedData['viaje_placa'] . '_' . Carbon::createFromFormat('d/m/Y', trim($validatedData['viaje_fecha_manifiesto']))->format('Y-m-d');
    
                // Definir la carpeta de almacenamiento "spreadsheets"
                $uploadPath = "spreadsheets";
                if (str_starts_with($mimeType, 'image')) {
                    $extension = $file->guessExtension(); // Obtener la extensión
                    $originalName = $baseName . '.' . $extension;
                } elseif ($mimeType === 'application/pdf') {
                    $originalName = $baseName . '.pdf';
                } else {
                    return response()->json([
                        'estatus_update' => 0,
                        'mensaje' => 'El archivo debe ser una imagen o un PDF',
                    ], 422);
                }
    
                // Eliminar el archivo antiguo si existe
                if ($route->viaje_planilla) {
                    $oldFilePath = public_path($uploadPath . '/' . $route->viaje_planilla);
                    if (file_exists($oldFilePath)) {
                        unlink($oldFilePath); // Eliminar archivo antiguo
                    }
                }
    
                // Mover el nuevo archivo a la carpeta "spreadsheets"
                $file->move($uploadPath, $originalName);
                $route->viaje_planilla = $originalName;
            }
    
            // Formatear las fechas
            $fecha_manifiesto = Carbon::createFromFormat('d/m/Y', trim($validatedData['viaje_fecha_manifiesto']))->format('Y-m-d');
            $fecha_inicio = Carbon::createFromFormat('d/m/Y', trim($validatedData['viaje_fecha_inicio']))->format('Y-m-d');
    
            // Actualizar el resto de los datos
            $route->fo_viaje_usuario = $validatedData['fo_viaje_usuario'];
            $route->fo_viaje_transportadora = $request->fo_viaje_transportadora;
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
            $route->viaje_total_ganancias = ($validatedData['viaje_neto_pago'] + $validatedData['viaje_sobrecosto']) - $validatedData['viaje_porcentaje_conductor']; // depende de una operación
            $route->viaje_estatus = $validatedData['viaje_estatus'];
            $route->viaje_observaciones = $validatedData['viaje_observaciones'];
            $route->save();
    
            return response()->json([
                'estatus_update' => 1,
                'mensaje' => 'Datos actualizados correctamente. :)'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'estatus_update' => 0,
                'mensaje' => 'Uno o más datos son incorrectos',
                'error' => $e->getMessage()
            ], 422);
        }
    }
    
    

    /**
     * Remove the specified resource from storage.
     */
    public function delete($viaje_id)
    {
        // Buscar el viaje
        $route = RoutesModel::find($viaje_id);
    
        // Buscar los gastos asociados (individuales y globales)
        $globalEgress = GlobalEgressModel::where('fo_gasto_g_viaje', $viaje_id)->get();
        $egress = EgressModel::where('fo_egreso_viaje', $viaje_id)->get();
        
        if ($route) {
            // Eliminar egresos individuales si existen
            if (!$egress->isEmpty()) {
                foreach ($egress as $single_item) {
                    $single_item->delete();
                }
            }
    
            // Eliminar gastos globales si existen
            if (!$globalEgress->isEmpty()) {
                foreach ($globalEgress as $global_item) {
                    $global_item->delete();
                }
            }
    
            // Verificar si hay un archivo planilla asociado
            if ($route->viaje_planilla) {
                // Definir el camino en la carpeta "spreadsheets" para el archivo
                $filePath = public_path("spreadsheets/" . $route->viaje_planilla);
    
                // Eliminar el archivo si existe en la carpeta "spreadsheets"
                if (file_exists($filePath)) {
                    unlink($filePath); 
                }
            }
    
            // Eliminar el registro del viaje
            $route->delete();
            
            return response()->json([
                "message" => "Registro, planilla y gastos consecuentes eliminados correctamente."
            ]);
        } else {
            return response()->json([
                "message" => "Viaje no encontrado."
            ], 404);
        }
    }
    
    public function downloadSpreadsheet($filename)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }
        
        $path = public_path('spreadsheets/' . $filename);
        
        if (!File::exists($path)) {
            return response()->json(['message' => 'Archivo no encontrado'], 404);
        }
        
        return response()->download($path)->setStatusCode(200);

    }
    public function getDriverName($fo_viaje_usuario)
    {
        $driver = RoutesModel::where('fo_viaje_usuario', $fo_viaje_usuario)
        ->join('usuarios', 'viajes.fo_viaje_usuario', '=', 'usuarios.usuario_id')
        ->select('usuarios.nombre_usuario as viaje_conductor')
        ->first();
    
        $driverName = $driver->viaje_conductor;
        return response()->json($driverName, 200);
    }

    public function finishRoute($viaje_id)
    {
        $route = RoutesModel::find($viaje_id);
        if ($route) {
            $route->viaje_fecha_llegada = Carbon::now();
            $route->save();
            return response()->json([
                'message' => 'Viaje finalizado correctamente'
            ]);
        } else {
            return response()->json([
                'message' => 'Viaje no encontrado'
            ], 404);
        }
    }
    
    
    
     
}
