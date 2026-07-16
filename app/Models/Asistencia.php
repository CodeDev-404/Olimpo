<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asistencia extends Model
{
    protected $table = 'asistencia';

    protected $fillable = [
        'persona_id', 'persona_nombre', 'fecha', 'hora_entrada',
        'hora_salida', 'turno', 'tardanza_min', 'etiqueta', 'horas_trabajadas'
    ];

    public function persona()
    {
        return $this->belongsTo(Personal::class, 'persona_id');
    }
}
