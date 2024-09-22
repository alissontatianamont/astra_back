<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlobalEgressModel extends Model
{
    use HasFactory;
    protected $table = 'gastos_globales';
    public $timestamps = false;
    protected $primaryKey = 'gasto_g_id';
    protected $fillable = [
        'gasto_g_descripcion',
        'fo_gasto_g_viaje',
        'gasto_g_valor',
    ];
}
