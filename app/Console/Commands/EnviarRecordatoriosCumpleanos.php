<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cumpleano;
use Illuminate\Support\Facades\Log;

class EnviarRecordatoriosCumpleanos extends Command
{
    protected $signature = 'app:enviar-recordatorios-cumpleanos';
    protected $description = 'Envía recordatorios de cumpleaños del día a la hora configurada';

    public function handle(): int
    {
        $hoy = now()->format('d/m');
        
        $cumpleanos = Cumpleano::where('fecha', $hoy)
            ->where('recordatorio_activo', true)
            ->whereNotNull('recordatorio_hora')
            ->get();

        if ($cumpleanos->isEmpty()) {
            $this->info('No hay cumpleaños con recordatorio activo para hoy (' . $hoy . ')');
            return 0;
        }

        $enviados = 0;
        foreach ($cumpleanos as $cumple) {
            Log::info("Recordatorio cumpleaños: {$cumple->nombre} ({$cumple->parentesco}) - {$cumple->detalles}");
            $enviados++;
        }

        $this->info("Se procesaron {$enviados} recordatorio(s) para hoy ({$hoy})");
        return 0;
    }
}
