<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RecordatorioProgramado;
use Illuminate\Support\Facades\Log;

class EnviarRecordatoriosProgramados extends Command
{
    protected $signature = 'app:enviar-recordatorios-programados
                            {--marcar-enviado : Marca como enviados los recordatorios vencidos}';

    protected $description = 'Loguea (y opcionalmente marca) los recordatorios programados vencidos del día';

    public function handle(): int
    {
        $hora = now()->format('H:i:s');

        $vencidos = RecordatorioProgramado::whereDate('fecha', now()->toDateString())
            ->where('enviado', false)
            ->where('hora', '<=', $hora)
            ->with('cumpleano')
            ->get();

        if ($vencidos->isEmpty()) {
            $this->info('No hay recordatorios programados vencidos para hoy');
            return 0;
        }

        foreach ($vencidos as $r) {
            $nombre = $r->cumpleano?->nombre ?? 'Desconocido';
            Log::info("Recordatorio programado: {$nombre} a las {$r->hora}");
            $this->info("→ {$nombre} a las {$r->hora}");
            if ($this->option('marcar-enviado')) {
                $r->update(['enviado' => true]);
            }
        }

        $this->info('Se procesaron ' . $vencidos->count() . ' recordatorio(s) programado(s)');
        return 0;
    }
}
