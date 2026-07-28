<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ControlVehiculo extends Model
{
    protected $fillable = [
        'fecha', 'chofer', 'placa', 'marca', 'modelo', 'clase',
        'hora_salida', 'km_salida', 'hora_ingreso', 'km_ingreso', 'observacion',
    ];
}
