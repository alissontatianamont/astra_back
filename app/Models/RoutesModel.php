<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoutesModel extends Model
{
    use HasFactory;
    protected $table = 'viajes';
    public $timestamps = false;
    protected $primaryKey = 'viaje_id';
    protected $fillable = [
        'fo_viaje_usuario',
        'fo_viaje_transportadora',
        'viaje_destino_inicio',
        'viaje_destino_llegada',
        'viaje_fecha_inicio',
        'viaje_fecha_llegada',
        'viaje_planilla',
        'viaje_total_gastos',
        'viaje_total_ganancias',
        'viaje_estatus'
    ];
}
