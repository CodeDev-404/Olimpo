<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Camioneta extends Model
{
    protected $fillable = ['placa', 'marca', 'modelo', 'anio', 'color', 'estado'];
}
