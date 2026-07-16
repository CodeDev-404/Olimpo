<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ocurrencia extends Model
{
    protected $fillable = [
        'fecha', 'hora_ingreso', 'hora_salida', 'persona_id',
        'persona_nombre', 'tipo', 'otro', 'detalles', 'observacion', 'turno', 'mes', 'anio'
    ];

    public function persona()
    {
        return $this->belongsTo(Personal::class, 'persona_id');
    }

    public function tipoOcurrencia()
    {
        return $this->belongsTo(TipoOcurrencia::class, 'tipo', 'nombre');
    }

    public function scopeDeUltimas24h($query)
    {
        $hoy = now()->format('d/m/Y');
        $ayer = now()->subDay()->format('d/m/Y');
        return $query->whereIn('fecha', [$hoy, $ayer])->orderBy('fecha', 'desc')->orderBy('id', 'desc');
    }
}
