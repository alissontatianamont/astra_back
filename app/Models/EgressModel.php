<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EgressModel extends Model
{
    use HasFactory;
    protected $table = 'egresos';
    public $timestamps = false;
    protected $primaryKey = 'egreso_id';
    protected $fillable = [
        'fo_egreso_viaje',
        'egreso_global',
        'egreso_exogenable',
        'fo_egreso_gasto_global',
        'fo_egreso_proveedor',
        'egreso_descripcion',
        'egreso_valor'
    ];
}
