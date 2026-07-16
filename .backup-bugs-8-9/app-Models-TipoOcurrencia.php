<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoOcurrencia extends Model
{
    protected $table = 'tipos_ocurrencia';

    protected $fillable = ['nombre', 'nivel', 'color', 'activo'];

    protected $casts = ['activo' => 'boolean'];

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function ocurrencias()
    {
        return $this->hasMany(Ocurrencia::class, 'tipo', 'nombre');
    }
}
