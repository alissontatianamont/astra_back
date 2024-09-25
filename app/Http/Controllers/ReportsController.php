<?php

namespace App\Http\Controllers;

use App\Models\ReportsModel;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportExport;

class ReportsController extends Controller
{
    protected $reportsModel;
    public function __construct() {
        $this->reportsModel = new ReportsModel();
    }
    /**
     * Display a listing of the resource.
     */
    public function getReportsName()
    {
        return $this->reportsModel->getReportsName();
    }
    
    public function getReport($rep_id, $date_start, $date_end)
    {
        $report = $this->reportsModel->getReport($rep_id);

        // Verificar si el reporte existe
        if (!$report) {
            return response()->json(['error' => 'Reporte no encontrado'], 404);
        }

        // Obtener la consulta SQL
        $sql = $report->rep_sql;
        if (!empty($date_start) && !empty($date_end)) {
            $sql = str_replace('{DESDE}', $date_start, $sql);
            $sql = str_replace('{HASTA}', $date_end, $sql);
            // Ejecutar la consulta SQL
            $results = DB::select($sql);

            // Devolver los resultados como JSON
            return response()->json($results);
        } else {
            return response()->json(['error' => 'Fechas no encontradas', 'status_request' => 0],);
        }
    }


    public function getReportDownload($rep_id, $date_start, $date_end)
    {
        // Recuperar el nombre y la SQL del reporte
        $report = $this->reportsModel->getReport($rep_id);

        // Verificar si el reporte existe
        if (!$report) {
            return ['error' => 'Reporte no encontrado']; // Cambiar aquí
        }

        // Obtener la consulta SQL
        $sql = $report->rep_sql;
        if (!empty($date_start) && !empty($date_end)) {
            $sql = str_replace('{DESDE}', $date_start, $sql);
            $sql = str_replace('{HASTA}', $date_end, $sql);

            // Ejecutar la consulta SQL
            $results = DB::select($sql);

            return $results; 
        } else {
            return ['error' => 'Fechas no encontradas']; 
        }
    }

    public function downloadReport($rep_id, $date_start, $date_end)
    {
        // Obtener los datos del reporte
        $data = $this->getReportDownload($rep_id, $date_start, $date_end);
    
        // Verificar si ocurrió algún error en los datos del reporte
        if (isset($data['error'])) {
            return response()->json(['error' => $data['error']], 400);
        }
    
        // Convertir los resultados en un array adecuado para la exportación
        $arrayData = array_map(function ($item) {
            return (array) $item; // Convertir stdClass a array
        }, $data);
    
        // Obtener los nombres de las columnas (encabezados)
        $headings = array_keys((array)$data[0]); // Suponiendo que hay al menos un elemento en $data
    
        // Recuperar el nombre del reporte
        $reportName = $this->reportsModel->select('rep_nombre')
            ->where('rep_id', $rep_id)
            ->first();
    
        // Descargar el archivo Excel con los encabezados
        return Excel::download(new ReportExport($arrayData, $headings), $reportName->rep_nombre . '_' . date('Y-m-d_H:i:s') . '.xlsx');
    }
    
    public function downloadExogenousReport()
    {
        // Recuperar el reporte específico con ID 2
        $report = $this->reportsModel->getExogenousReport();
    
        if (!$report) {
            return response()->json(['error' => 'Reporte no encontrado'], 404);
        }
    
        $sql = $report->rep_sql;
    
        $results = DB::select($sql);
    
        $arrayData = array_map(function ($item) {
            return (array) $item; // Convertir stdClass a array
        }, $results);
    
        $headings = array_keys((array)$results[0]);
        return Excel::download(new ReportExport($arrayData, $headings), $report->rep_nombre . '_' . date('Y-m-d_H:i:s') . '.xlsx');
    }
}
