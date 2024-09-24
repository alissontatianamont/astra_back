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

    public function getExogenous()
    {
        $exogenous = $this->where('exogena_estatus', 1)->get();
        $exogenousArray = $exogenous->toArray();
        return response()->json($exogenousArray, 200);
    }
    public function getExogenousSelect()
    {
        $exogenous = $this->where('exogena_estatus', 1)->select('exogena_id', 'exogena_nombre1', 'exogena_nombre2', 'exogena_apellido1', 'exogena_apellido2', 'exogena_razon_social')->get();
        $exogenousArray = $exogenous->toArray();
        return response()->json($exogenousArray, 200);
    }

    public function saveExogenous($validatedData)
    {
        $this->exogena_nit = $validatedData['exogena_nit'];
        $this->exogena_dv = $validatedData['exogena_dv'];
        $this->exogena_nombre1 = $validatedData['exogena_nombre1'];
        $this->exogena_nombre2 = $validatedData['exogena_nombre2'];
        $this->exogena_apellido1 = $validatedData['exogena_apellido1'];
        $this->exogena_apellido2 = $validatedData['exogena_apellido2'];
        $this->exogena_razon_social = $validatedData['exogena_razon_social'];
        $this->exogena_direccion = $validatedData['exogena_direccion'];
        $this->exogena_ciudad = $validatedData['exogena_ciudad'];
        $this->exogena_departamento = $validatedData['exogena_departamento'];
        $this->exogena_tipo = $validatedData['exogena_tipo'];
        $this->exogena_estatus = $validatedData['exogena_estatus'];
        $this->save();
    }

    public function getExogenousById($exogenous_id)
    {
        return $this->find($exogenous_id);
    }

    public function updateExogenous($exogenous_id, $validatedData)
{
    $exogenous = self::find($exogenous_id);
    $exogenous->exogena_nit = $validatedData['exogena_nit'];
    $exogenous->exogena_dv = $validatedData['exogena_dv'];
    $exogenous->exogena_nombre1 = $validatedData['exogena_nombre1'];
    $exogenous->exogena_nombre2 = $validatedData['exogena_nombre2'];
    $exogenous->exogena_apellido1 = $validatedData['exogena_apellido1'];
    $exogenous->exogena_apellido2 = $validatedData['exogena_apellido2'];
    $exogenous->exogena_razon_social = $validatedData['exogena_razon_social'];
    $exogenous->exogena_direccion = $validatedData['exogena_direccion'];
    $exogenous->exogena_ciudad = $validatedData['exogena_ciudad'];
    $exogenous->exogena_departamento = $validatedData['exogena_departamento'];
    $exogenous->exogena_tipo = $validatedData['exogena_tipo'];
    $exogenous->exogena_estatus = $validatedData['exogena_estatus'];
    $exogenous->save();
}

}
