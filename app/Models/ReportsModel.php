<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportsModel extends Model
{
    use HasFactory;
    protected $table = 'rep_reportes';
    public $timestamps = false;
    protected $primaryKey = 'rep_id';
    protected $fillable = [
        'rep_nombre',
        'rep_sql',
        'rep_status',
        'rep_fecha_creacion'
    ];

    public function getReportsName()
    {
        $reportsName = $this->select('rep_id', 'rep_nombre')->where('rep_status', 1)->get();
        return response()->json($reportsName);
    }
    public function getReport($rep_id){
        $report = $this->select('rep_nombre', 'rep_sql')
        ->where('rep_id', $rep_id)
        ->first();
        return $report;
    }

    public function getExogenousReport()
    {
        $report = $this->select('rep_nombre', 'rep_sql')
        ->where('rep_id', 2)
        ->first();
        return $report;
    }
}
