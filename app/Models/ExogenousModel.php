<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExogenousModel extends Model
{
    use HasFactory;
    protected $table = 'exogenas';
    public $timestamps = false;
    protected $primaryKey = 'exogena_id';
    protected $fillable = [
        'exogena_nit',
        'exogena_dv',
        'exogena_nombre1',
        'exogena_nombre2',
        'exogena_apellido1',
        'exogena_apellido2',
        'exogena_razon_social',
        'exogena_direccion',
        'exogena_ciudad',
        'exogena_departamento',
        'exogena_tipo',
        'exogena_estatus'
    ];

}
