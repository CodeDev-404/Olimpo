<?php

namespace App\Livewire\Olimpo;

use Livewire\Component;
use App\Models\Ocurrencia;
use App\Models\TipoOcurrencia;
use Illuminate\Support\Facades\DB;

class Graficos extends Component
{
    public $anio;
    public $chartData = null;
    public $chartPieData = null;
    public $hasData = false;

    protected $listeners = ['panelChanged' => '$refresh'];

    public function mount()
    {
        $this->anio = now()->year;
    }

    public function generar()
    {
        // Barras: Ocurrencias por persona
        $rows = DB::table('ocurrencias')
            ->select('persona_nombre', DB::raw('COUNT(*) as total'))
            ->where('anio', (int)$this->anio)
            ->groupBy('persona_nombre')
            ->orderByDesc('total')
            ->get();

        if ($rows->isEmpty()) {
            $this->hasData = false;
            return;
        }

        $this->hasData = true;
        $nombres = $rows->pluck('persona_nombre')->map(fn($n) => explode(' ', $n)[0])->toArray();
        $totales = $rows->pluck('total')->toArray();

        $this->chartData = [
            'labels' => $nombres,
            'data' => $totales,
            'title' => "Ocurrencias por Personal - Año {$this->anio}",
        ];

        // Pastel: Distribución por tipo (mes actual)
        $mesActual = now()->month;
        $pieRows = DB::table('ocurrencias')
            ->select('tipo', DB::raw('COUNT(*) as total'))
            ->where('mes', $mesActual)
            ->where('anio', (int)$this->anio)
            ->groupBy('tipo')
            ->get();

        if ($pieRows->isNotEmpty()) {
            $this->chartPieData = [
                'labels' => $pieRows->pluck('tipo')->toArray(),
                'data' => $pieRows->pluck('total')->toArray(),
                'title' => "Distribución por Tipo - {$mesActual}/{$this->anio}",
            ];
        }
    }

    public function render()
    {
        return view('livewire.olimpo.graficos')
            ->layout('layouts.olimpo', ['title' => 'Gráficos']);
    }
}