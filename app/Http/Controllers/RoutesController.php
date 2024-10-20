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
use Illuminate\Support\Facades\Storage;

class RoutesController extends Controller
{
    protected $routeModel;
    public function __construct() {
        $this->routeModel = new RoutesModel();
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
            $routes = $this->routeModel->getRoutes();
            $routesArray = $routes->toArray();
            return response()->json($routesArray, 200);
    }


    public function getRoutesByUser($usuario_id)
    {
        $routes = $this->routeModel->getRoutesByUser($usuario_id);
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
     
             // Manejo del archivo
             $file = $request->file("viaje_planilla");
             $originalName = '';
     
             if ($file) {
                 $formattedDate = str_replace('/', '-', $validatedData['viaje_fecha_manifiesto']);
                 $baseName = "manifiesto_" . $validatedData['viaje_num_manifiesto'] . '_' . $validatedData['viaje_placa'] . '_' . $formattedDate;
                 $extension = $file->guessExtension();
                 $originalName = $baseName . '.' . $extension;
                 $uploadPath = 'spreadsheets';
                 
                 // Almacenar el archivo en storage/app/spreadsheets
                 $file->storeAs($uploadPath, $originalName);
             } else {
                 $originalName = null;
             }
     
             // Llamar a la función del modelo para guardar los datos
             $route = $this->routeModel->saveRoute($validatedData, $originalName, $request->fo_viaje_transportadora);
     
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
        $route =  $this->routeModel->getRoute($viaje_id);
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
            $route = $this->routeModel->getRoute($viaje_id);
            if (!$route) {
                return response()->json([
                    'estatus_update' => 0,
                    'mensaje' => 'El viaje no fue encontrado',
                ], 404);
            }
    
            // Manejo del archivo
            $originalName = null;
            if ($request->hasFile('viaje_planilla')) {
                $file = $request->file('viaje_planilla');
    
                // Obtener el tipo de archivo
                $mimeType = $file->getClientMimeType();
                
                // Reemplazar las barras (/) en la fecha por guiones (-)
                $formattedDate = str_replace('/', '-', $validatedData['viaje_fecha_manifiesto']);
                $baseName = "manifiesto_" . $validatedData['viaje_num_manifiesto'] . '_' . $validatedData['viaje_placa'] . '_' . $formattedDate;
    
                // Definir la carpeta de almacenamiento "spreadsheets"
                $uploadPath = 'spreadsheets';
                if (str_starts_with($mimeType, 'image')) {
                    $extension = $file->guessExtension();
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
                    $oldFilePath = 'spreadsheets/' . $route->viaje_planilla;
                    if (Storage::exists($oldFilePath)) {
                        Storage::delete($oldFilePath); // Eliminar archivo antiguo
                    }
                }
    
                // Guardar el nuevo archivo en la carpeta "spreadsheets" en storage
                $file->storeAs($uploadPath, $originalName);
            }
    
            // Llamar a la función del modelo para actualizar los datos
            $this->routeModel->updateRoute($validatedData, $originalName, $request->fo_viaje_transportadora, $viaje_id);
    
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
        $route = $this->routeModel->getRoute($viaje_id);
    
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
        
        // Definir la ruta en el almacenamiento
        $path = 'spreadsheets/' . $filename;
    
        // Verificar si el archivo existe en el storage
        if (!Storage::exists($path)) {
            return response()->json(['message' => 'Archivo no encontrado'], 404);
        }
    
        // Descargar el archivo desde el storage
        return Storage::download($path);
    }
    
    
    public function getDriverName($fo_viaje_usuario)
    {
       $driver = $this->routeModel->getDriverName($fo_viaje_usuario);
    
        $driverName = $driver->viaje_conductor;
        return response()->json($driverName, 200);
    }

    public function finishRoute($viaje_id)
    {
        $route =  $this->routeModel->getRoute($viaje_id);
        if ($route) {
            $route->viaje_fecha_llegada = Carbon::now();
            $route->save();
            return response()->json([
                "date" => date('Y-m-d H:i:s'),                
                'message' => 'Viaje finalizado correctamente'
            ]);
        } else {
            return response()->json([
                'message' => 'Viaje no encontrado'
            ], 404);
        }
    }
    
    
     
}
