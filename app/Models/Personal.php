<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Personal extends Model
{
    protected $table = 'personal';

    protected $fillable = [
        'nombre', 'segundo_nombre', 'apellido_paterno', 'apellido_materno',
        'cargo', 'cargo_id', 'departamento', 'documento', 'fecha_nacimiento',
        'telefono', 'email', 'estado', 'hora_entrada', 'hora_salida',
        'alias'
    ];

    public function cargoRel()
    {
        return $this->belongsTo(Cargo::class, 'cargo_id');
    }

    public function scopeActivos($query)
    {
        return $query->where('estado', 'ACTIVO');
    }

    public function ocurrencias()
    {
        return $this->hasMany(Ocurrencia::class, 'persona_id');
    }

    public function asistencias()
    {
        return $this->hasMany(Asistencia::class, 'persona_id');
    }
}
