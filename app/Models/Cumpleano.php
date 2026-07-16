<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cumpleano extends Model
{
    protected $table = 'cumpleanos';

    protected $fillable = [
        'fecha', 'nombre', 'dni', 'parentesco', 'detalles', 'recordatorio_activo', 'recordatorio_hora',
    ];
}
