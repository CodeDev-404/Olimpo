<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecordatorioProgramado extends Model
{
    protected $table = 'recordatorios_programados';

    protected $fillable = [
        'cumpleano_id', 'fecha', 'hora', 'enviado',
    ];

    protected $casts = [
        'fecha' => 'date',
        'enviado' => 'boolean',
    ];

    public function cumpleano(): BelongsTo
    {
        return $this->belongsTo(Cumpleano::class);
    }
}
