<?php

namespace App\Livewire\Olimpo;

use Livewire\Component;
use App\Models\Cumpleano;
use App\Models\RecordatorioProgramado;

class RecordatoriosGlobal extends Component
{
    public array $due = [];

    public function mount(): void
    {
        $this->refresh();
    }

    public function refresh(): void
    {
        $cumpleanos = Cumpleano::where('fecha', now()->format('d/m'))
            ->where('recordatorio_activo', true)
            ->get()
            ->map(fn($c) => [
                'tipo' => 'cumple',
                'id' => $c->id,
                'nombre' => $c->nombre,
                'parentesco' => $c->parentesco ?? '',
                'hora' => $c->recordatorio_hora ? substr($c->recordatorio_hora, 0, 5) : '07:30',
            ]);

        $programados = RecordatorioProgramado::whereDate('fecha', now()->toDateString())
            ->where('enviado', false)
            ->where('hora', '<=', now()->format('H:i:s'))
            ->get();

        $programados->each->update(['enviado' => true]);

        $programadosMap = $programados->map(fn($r) => [
            'tipo' => 'prog',
            'id' => $r->id,
            'nombre' => $r->cumpleano?->nombre ?? '',
            'parentesco' => $r->cumpleano?->parentesco ?? '',
            'hora' => substr($r->hora, 0, 5),
        ]);

        $this->due = $cumpleanos->concat($programadosMap)->values()->toArray();
    }

    public function render()
    {
        return view('livewire.olimpo.recordatorios-global');
    }
}
