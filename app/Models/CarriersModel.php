<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\RoutesModel;

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

    private $RoutesModel;
    public function __construct() {
        $this->RoutesModel = new RoutesModel();
    }
    public function getCarriers()
    {
        $carriers = CarriersModel::where('transportadora_estatus', 1)->get();
        $carriersArray = $carriers->toArray();
        return response()->json($carriersArray, 200);
    }
    public function saveCarrier($validatedData)
    {
        $this->transportadora_razon_social = $validatedData['transportadora_razon_social'];
        $this->transportadora_nit = $validatedData['transportadora_nit'];
        $this->transportadora_direccion = $validatedData['transportadora_direccion'];
        $this->transportadora_telefono = $validatedData['transportadora_telefono'];
        $this->transportadora_dv = $validatedData['transportadora_dv'];
        $this->transportadora_ciudad = $validatedData['transportadora_ciudad'];
        $this->transportadora_departamento = $validatedData['transportadora_departamento'];
        $this->transportadora_estatus = $validatedData['transportadora_estatus'];
        $this->save();
    }

    public function getCarrier($carrier_id)
    {
        $carrier = $this->find($carrier_id);
        return $carrier;
    }
    public function updateCarrier($validatedData)
    {
        $this->transportadora_razon_social = $validatedData['transportadora_razon_social'];
        $this->transportadora_nit = $validatedData['transportadora_nit'];
        $this->transportadora_direccion = $validatedData['transportadora_direccion'];
        $this->transportadora_telefono = $validatedData['transportadora_telefono'];
        $this->transportadora_dv = $validatedData['transportadora_dv'];
        $this->transportadora_ciudad = $validatedData['transportadora_ciudad'];
        $this->transportadora_departamento = $validatedData['transportadora_departamento'];
        $this->transportadora_estatus = $validatedData['transportadora_estatus'];
        $this->save();
    }
    public function validateCarriersRelatedRoutes($carrier_id)
    {
        $carrier = $this->RoutesModel->where('fo_viaje_transportadora', $carrier_id)->first();

        if ($carrier) {
            return 1; 
        } 
        return 0;
    }
}
