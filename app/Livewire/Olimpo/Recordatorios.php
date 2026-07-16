<?php

namespace App\Livewire\Olimpo;

use Livewire\Component;
use App\Models\Cumpleano;
use Carbon\Carbon;

class Recordatorios extends Component
{
    protected $listeners = ['panelChanged' => '$refresh'];

    public function render()
    {
        $today = now()->format('d/m');
        $todayMonth = (int) now()->format('m');
        $todayDay = (int) now()->format('d');
        $todayMd = ($todayMonth * 100) + $todayDay;

        $cumpleanos = Cumpleano::where('recordatorio_activo', true)->get()->map(function ($c) use ($today, $todayMd, $todayMonth, $todayDay) {
            $dayOfWeek = $this->dayOfWeekForYear($c->fecha);
            $fechaLarga = $this->fechaLarga($c->fecha);
            $proximidad = $this->proximidad($c->fecha, $todayMonth, $todayDay);
            $esHoy = $c->fecha === $today;

            return [
                'id' => $c->id,
                'fecha' => $c->fecha,
                'fecha_larga' => $fechaLarga,
                'nombre' => $c->nombre,
                'detalles' => $c->detalles,
                'parentesco' => $c->parentesco ?? '',
                'recordatorio_hora' => $c->recordatorio_hora ?? '07:30:00',
                'dia' => $dayOfWeek,
                'proximidad' => $proximidad,
                'es_hoy' => $esHoy,
            ];
        })->sortBy('proximidad')->values()->toArray();

        $hoy = array_values(array_filter($cumpleanos, fn($c) => $c['es_hoy']));
        $proximos = array_values(array_filter($cumpleanos, fn($c) => !$c['es_hoy']));

        return view('livewire.olimpo.recordatorios', [
            'cumpleanosHoy' => $hoy,
            'proximosRecordatorios' => $proximos,
        ])->layout('layouts.olimpo', ['title' => 'Recordatorios']);
    }

    private function proximidad(string $ddmm, int $currMonth, int $currDay): int
    {
        [$d, $m] = explode('/', $ddmm);
        $m = (int) $m;
        $d = (int) $d;
        $md = ($m * 100) + $d;
        $todayMd = ($currMonth * 100) + $currDay;

        if ($md >= $todayMd) {
            return $md - $todayMd;
        }
        return (1231 - $todayMd) + $md;
    }

    private function dayOfWeekForYear(string $ddmm, ?int $year = null): string
    {
        $year = $year ?? (int)now()->format('Y');
        [$d, $m] = explode('/', $ddmm);
        if (!checkdate((int)$m, (int)$d, $year)) return '—';
        $date = Carbon::create($year, (int)$m, (int)$d);
        $es = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
        return $es[$date->dayOfWeek];
    }

    private function fechaLarga(string $ddmm, ?int $year = null): string
    {
        $year = $year ?? (int)now()->format('Y');
        [$d, $m] = explode('/', $ddmm);
        if (!checkdate((int)$m, (int)$d, $year)) return $ddmm;
        $date = Carbon::create($year, (int)$m, (int)$d);
        $meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
        return $date->day . ' de ' . $meses[(int)$m - 1];
    }
}
