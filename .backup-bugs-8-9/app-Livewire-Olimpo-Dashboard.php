<?php

namespace App\Livewire\Olimpo;

use Livewire\Component;
use App\Models\Ocurrencia;
use App\Models\TipoOcurrencia;
use Illuminate\Support\Facades\Cache;

class Dashboard extends Component
{
    public $search = '';
    public $selectedId = null;
    public $stats = [];
    public $ocurrencias = [];

    protected $listeners = ['panelChanged' => 'refreshData'];

    public function mount()
    {
        $this->refreshData();
    }

    public function refreshData()
    {
        $this->loadStats();
        $this->loadOcurrencias();
    }

    public function loadStats()
    {
        $this->stats = Cache::remember('dashboard_stats', 300, function () {
            $hoy = now()->format('d/m/Y');
            $semana = collect(range(0, 6))->map(fn($d) => now()->subDays($d)->format('d/m/Y'));
            $mes = now()->month;
            $anio = now()->year;

            return [
                'hoy' => Ocurrencia::where('fecha', $hoy)->count(),
                'semana' => Ocurrencia::whereIn('fecha', $semana)->count(),
                'mes' => Ocurrencia::where('mes', $mes)->where('anio', $anio)->count(),
                'total' => Ocurrencia::count(),
            ];
        });
    }

    public function loadOcurrencias()
    {
        $query = Ocurrencia::query()
            ->leftJoin('personal', 'ocurrencias.persona_nombre', '=', 'personal.nombre')
            ->select('ocurrencias.*', 'personal.cargo as persona_cargo');

        if ($this->search) {
            $q = $this->search;
            $query->where(function($qry) use ($q) {
                $qry->where('ocurrencias.persona_nombre', 'like', "%{$q}%")
                    ->orWhere('ocurrencias.tipo', 'like', "%{$q}%")
                    ->orWhere('ocurrencias.detalles', 'like', "%{$q}%")
                    ->orWhere('ocurrencias.fecha', 'like', "%{$q}%")
                    ->orWhere('personal.cargo', 'like', "%{$q}%");
            });
            $this->ocurrencias = $query->orderBy('ocurrencias.fecha', 'desc')->orderBy('ocurrencias.id', 'desc')->get()->toArray();
        } else {
            $hoy = now()->format('d/m/Y');
            $ayer = now()->subDay()->format('d/m/Y');
            $this->ocurrencias = $query->whereIn('ocurrencias.fecha', [$hoy, $ayer])
                ->orderBy('ocurrencias.fecha', 'desc')->orderBy('ocurrencias.id', 'desc')->get()->toArray();
        }
    }

    public function selectOcurrencia($id)
    {
        $this->selectedId = $id;
    }

    public function updatedSearch()
    {
        $this->loadOcurrencias();
    }

    public function tipoColor($tipo)
    {
        $t = TipoOcurrencia::where('nombre', $tipo)->first();
        return $t ? $t->color : '#888899';
    }

    public function render()
    {
        return view('livewire.olimpo.dashboard')
            ->layout('layouts.olimpo', ['title' => 'Dashboard']);
    }
}
