<?php

namespace App\Livewire\Olimpo;

use Livewire\Component;
use App\Models\Ocurrencia;
use App\Models\Personal;
use App\Models\TipoOcurrencia;
use App\Models\Cumpleano;
use Illuminate\Support\Facades\Cache;

class Dashboard extends Component
{
    public $search = '';
    public $selectedId = null;
    public $stats = [];
    public $ocurrencias = [];
    public $cumpleanos = [];

    public $configHoraEntradaDia = '08:00';
    public $configHoraSalidaDia = '17:00';
    public $configHoraEntradaNoche = '19:00';
    public $configHoraSalidaNoche = '07:00';

    public $showQuickForm = false;
    public $qf_persona_nombre = '';
    public $qf_tipo = '';
    public $qf_otro = '';
    public $qf_detalles = '';
    public $qf_observacion = '';
    public $qf_turno = 'DÍA';
    public $qf_hora_ingreso = '';
    public $qf_hora_salida = '';

    protected $listeners = ['panelChanged' => 'refreshData', 'ocurrenciaCreada' => 'refreshData'];

    public function mount()
    {
        $this->loadConfig();
        $this->refreshData();
    }

    private function loadConfig()
    {
        $config = \DB::table('configuracion')->get()->keyBy('clave');
        $this->configHoraEntradaDia = $config['hora_entrada_dia']->valor ?? '08:00';
        $this->configHoraSalidaDia = $config['hora_salida_dia']->valor ?? '17:00';
        $this->configHoraEntradaNoche = $config['hora_entrada_noche']->valor ?? '19:00';
        $this->configHoraSalidaNoche = $config['hora_salida_noche']->valor ?? '07:00';
    }

    private function detectarTurnoActual(): string
    {
        $now = now('America/Lima');
        $actualMin = (int)$now->format('H') * 60 + (int)$now->format('i');

        $diaEnt = explode(':', $this->configHoraEntradaDia);
        $diaSal = explode(':', $this->configHoraSalidaDia);
        $diaEntMin = (int)$diaEnt[0] * 60 + (int)($diaEnt[1] ?? 0);
        $diaSalMin = (int)$diaSal[0] * 60 + (int)($diaSal[1] ?? 0);

        $nocheEnt = explode(':', $this->configHoraEntradaNoche);
        $nocheSal = explode(':', $this->configHoraSalidaNoche);
        $nocheEntMin = (int)$nocheEnt[0] * 60 + (int)($nocheEnt[1] ?? 0);
        $nocheSalMin = (int)$nocheSal[0] * 60 + (int)($nocheSal[1] ?? 0);

        $enRango = function($entMin, $salMin, $actualMin) {
            if ($salMin < $entMin) {
                return $actualMin >= $entMin || $actualMin < $salMin;
            }
            return $actualMin >= $entMin && $actualMin < $salMin;
        };

        if ($enRango($diaEntMin, $diaSalMin, $actualMin)) return 'DÍA';
        if ($enRango($nocheEntMin, $nocheSalMin, $actualMin)) return 'NOCHE';

        return 'DÍA';
    }

    public function refreshData()
    {
        $this->loadStats();
        $this->loadOcurrencias();
        $this->loadCumpleanos();
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

    public function loadCumpleanos()
    {
        $hoy = now()->format('d/m');
        $this->cumpleanos = Cumpleano::whereRaw("SUBSTR(fecha, 1, 5) = ?", [$hoy])
            ->orderBy('nombre')
            ->get()
            ->toArray();
    }

    public function loadOcurrencias()
    {
        $query = Ocurrencia::query()
            ->leftJoin('personal', 'ocurrencias.persona_id', '=', 'personal.id')
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
        $this->selectedId = $id === $this->selectedId ? null : $id;
    }

    public function tipoColor($tipo)
    {
        $t = TipoOcurrencia::where('nombre', $tipo)->first();
        return $t ? $t->color : '#888899';
    }

    public function newQuickOcurrencia()
    {
        $this->qf_persona_nombre = '';
        $this->qf_tipo = '';
        $this->qf_otro = '';
        $this->qf_detalles = '';
        $this->qf_observacion = '';
        $this->qf_turno = $this->detectarTurnoActual();
        $this->qf_hora_ingreso = $this->qf_turno === 'NOCHE' ? $this->configHoraEntradaNoche : $this->configHoraEntradaDia;
        $this->qf_hora_salida = $this->qf_turno === 'NOCHE' ? $this->configHoraSalidaNoche : $this->configHoraSalidaDia;
        $this->showQuickForm = true;
    }

    public function updatedQfTurno()
    {
        if (!$this->showQuickForm) return;
        $this->qf_hora_ingreso = $this->qf_turno === 'NOCHE' ? $this->configHoraEntradaNoche : $this->configHoraEntradaDia;
        $this->qf_hora_salida = $this->qf_turno === 'NOCHE' ? $this->configHoraSalidaNoche : $this->configHoraSalidaDia;
    }

    public function saveQuickOcurrencia()
    {
        $this->validate(['qf_persona_nombre' => 'required']);

        $parts = explode('/', now()->format('d/m/Y'));
        $persona = Personal::where('nombre', $this->qf_persona_nombre)->first();
        $tipoModel = $this->qf_tipo ? TipoOcurrencia::where('nombre', $this->qf_tipo)->first() : null;

        Ocurrencia::create([
            'fecha' => now()->format('d/m/Y'),
            'hora_ingreso' => $this->qf_hora_ingreso,
            'hora_salida' => $this->qf_hora_salida,
            'persona_nombre' => $this->qf_persona_nombre,
            'persona_id' => $persona?->id,
            'tipo' => $this->qf_tipo,
            'tipo_id' => $tipoModel?->id,
            'otro' => $this->qf_otro,
            'detalles' => $this->qf_detalles,
            'observacion' => $this->qf_observacion,
            'turno' => $this->qf_turno,
            'mes' => $parts[1],
            'anio' => $parts[2],
        ]);

        $this->showQuickForm = false;
        $this->dispatch('notify', message: 'Ocurrencia registrada.', type: 'success');
        $this->refreshData();
    }

    public function cancelQuickForm()
    {
        $this->showQuickForm = false;
    }

    public function updatedSearch()
    {
        $this->loadOcurrencias();
    }

    public function render()
    {
        return view('livewire.olimpo.dashboard', [
            'nombres' => Personal::activos()->pluck('nombre')->toArray(),
            'tipos' => TipoOcurrencia::activos()->pluck('nombre')->toArray(),
        ])->layout('layouts.olimpo', ['title' => 'Dashboard']);
    }
}
