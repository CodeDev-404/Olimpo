<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsultaHistorial extends Model
{
    protected $table = 'consulta_historial';

    protected $fillable = [
        'user_id',
        'tipo',
        'documento',
        'resultado_json',
        'nombre_mostrar',
    ];

    protected $casts = [
        'resultado_json' => 'json',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
