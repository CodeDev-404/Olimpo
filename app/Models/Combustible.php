<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Combustible extends Model
{
    protected $fillable = [
        'fecha', 'categoria', 'clase', 'marca', 'placa', 'modelo', 'anio',
        'color', 'conductor', 'kilometraje', 'combustible',
        'galones', 'precio_galon', 'total',
    ];
}
