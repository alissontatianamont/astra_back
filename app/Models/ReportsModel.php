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
}
