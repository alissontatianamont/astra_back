<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarriersModel extends Model
{
    use HasFactory;
    protected $table = 'transportadoras';
    public $timestamps = false;
    protected $primaryKey = 'transportadora_id';
    protected $fillable = [
        'transportadora_razon_social',
        'transportadora_nit',
        'transportadora_direccion',
        'transportadora_telefono',
        'transportadora_dv',
        'transportadora_ciudad',
        'transportadora_departamento',
        'transportadora_estatus',
    ];
}
