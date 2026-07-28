<?php

namespace App\Livewire\Olimpo;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use App\Models\Ocurrencia;
use App\Models\TipoOcurrencia;

class OcurrenciasTable extends Component
{
    use WithPagination;

    public $search = '';
    public $filterFecha = '';
    public $filterTurno = '';
    public $filterMesDesde = '';
    public $filterMesHasta = '';
    public $filterHoraDesde = '';
    public $filterHoraHasta = '';
    public $refreshKey = 0;
    public $selectMode = false;
    public $selectedIds = [];

    public $showNotaForm = false;
    public $nota_texto = '';
    public $notaFecha = '';
    public $notaHoraIngreso = '';
    public $notaHoraSalida = '';
    public $notaNombre = '';
    public $notaTipo = 'Nota';
    public $notaTurno = 'DÍA';

    protected $paginationTheme = 'tailwind';

    public function updated($property)
    {
        if (str_starts_with($property, 'filter') || $property === 'search') {
            $this->resetPage();
        }
    }

    #[Computed]
    public function getTipoColoresProperty()
    {
        return TipoOcurrencia::where('activo', true)
            ->pluck('color', 'nombre')
            ->toArray();
    }

    public function tipoColor($tipo)
    {
        return $this->tipoColores[$tipo] ?? '#888899';
    }

    public function toggleSelectMode()
    {
        $this->selectMode = !$this->selectMode;
        if (!$this->selectMode) $this->selectedIds = [];
    }

    public function toggleSelect($id)
    {
        $key = array_search($id, $this->selectedIds);
        if ($key !== false) {
            unset($this->selectedIds[$key]);
        } else {
            $this->selectedIds[] = $id;
        }
    }

    public function toggleSelectAll()
    {
        $ids = $this->getOcurrenciasIds();
        $allSelected = !array_diff($ids, $this->selectedIds);
        if ($allSelected) {
            $this->selectedIds = array_diff($this->selectedIds, $ids);
        } else {
            $this->selectedIds = array_merge($this->selectedIds, $ids);
        }
    }

    #[On('activate-nota-form')]
    public function nuevaNota()
    {
        $this->resetValidation();
        $this->reset(['nota_texto', 'notaHoraIngreso', 'notaHoraSalida', 'notaNombre']);
        $this->notaFecha = now()->format('d/m/Y');
        $this->notaTurno = 'DÍA';
        $this->notaTipo = 'Nota';
        $this->showNotaForm = true;
    }

    public function cancelarNota()
    {
        $this->resetValidation();
        $this->showNotaForm = false;
    }

    public function guardarNota()
    {
        $this->validate([
            'nota_texto' => 'required|string|max:1000',
            'notaFecha' => ['required', 'string', function ($attr, $value, $fail) {
                try {
                    \Carbon\Carbon::createFromFormat('d/m/Y', $value);
                } catch (\Exception $e) {
                    $fail('La fecha no tiene un formato válido (dd/mm/aaaa).');
                }
            }],
            'notaTurno' => 'required|in:DÍA,NOCHE',
            'notaTipo' => 'required|in:Nota,Importante',
        ]);

        $fecha = \Carbon\Carbon::createFromFormat('d/m/Y', $this->notaFecha);
        $data = [
            'fecha' => $this->notaFecha,
            'mes' => (int)$fecha->month,
            'anio' => (int)$fecha->year,
            'turno' => $this->notaTurno,
            'nota_texto' => $this->nota_texto,
            'user_id' => auth()->id(),
            'es_nota' => true,
        ];

        if ($this->notaHoraIngreso)
            $data['hora_ingreso'] = $this->notaHoraIngreso;
        if ($this->notaHoraSalida)
            $data['hora_salida'] = $this->notaHoraSalida;
        $data['persona_nombre'] = $this->notaNombre ?: '';
        $data['tipo'] = $this->notaTipo ?: '';

        Ocurrencia::create($data);

        $this->showNotaForm = false;
        $this->dispatch('nota-guardada');
    }

    protected function getOcurrenciasIds(): array
    {
        $query = $this->buildQuery();
        return $query->orderBy('ocurrencias.fecha', 'desc')
            ->orderBy('ocurrencias.id', 'desc')
            ->pluck('ocurrencias.id')
            ->toArray();
    }

    protected function buildQuery()
    {
        $query = Ocurrencia::query()
            ->leftJoin('personal', 'ocurrencias.persona_id', '=', 'personal.id')
            ->leftJoin('users', 'ocurrencias.user_id', '=', 'users.id')
            ->select('ocurrencias.*', 'personal.cargo as persona_cargo', 'personal.alias as persona_alias', 'users.name as usuario_nombre');

        if ($this->filterTurno) {
            $query->where('ocurrencias.turno', $this->filterTurno);
        }

        if ($this->filterHoraDesde) {
            $query->where('ocurrencias.hora_ingreso', '>=', $this->filterHoraDesde);
        }
        if ($this->filterHoraHasta) {
            $query->where('ocurrencias.hora_ingreso', '<=', $this->filterHoraHasta);
        }

        if ($this->search) {
            $q = $this->search;
            $query->where(function ($qry) use ($q) {
                $qry->where('ocurrencias.persona_nombre', 'like', "%{$q}%")
                    ->orWhere('ocurrencias.tipo', 'like', "%{$q}%")
                    ->orWhere('ocurrencias.detalles', 'like', "%{$q}%")
                    ->orWhere('ocurrencias.otro', 'like', "%{$q}%")
                    ->orWhere('ocurrencias.destino', 'like', "%{$q}%")
                    ->orWhere('ocurrencias.motivo', 'like', "%{$q}%")
                    ->orWhere('ocurrencias.vehiculo', 'like', "%{$q}%")
                    ->orWhere('ocurrencias.nota_texto', 'like', "%{$q}%")
                    ->orWhere('ocurrencias.observacion', 'like', "%{$q}%")
                    ->orWhere('ocurrencias.fecha', 'like', "%{$q}%")
                    ->orWhere('personal.cargo', 'like', "%{$q}%")
                    ->orWhere('personal.alias', 'like', "%{$q}%")
                    ->orWhere('personal.documento', 'like', "%{$q}%")
                    ->orWhere('personal.telefono', 'like', "%{$q}%");
            });
        }

        if ($this->search || $this->filterFecha
            || $this->filterMesDesde || $this->filterMesHasta) {
            if ($this->filterFecha) {
                $f = \Carbon\Carbon::createFromFormat('d/m/Y', $this->filterFecha);
                $query->where('ocurrencias.fecha', $f->format('d/m/Y'));
            }
            if ($this->filterMesDesde) {
                $query->where('ocurrencias.mes', '>=', (int)$this->filterMesDesde);
            }
            if ($this->filterMesHasta) {
                $query->where('ocurrencias.mes', '<=', (int)$this->filterMesHasta);
            }
        } else {
            $hoy = now()->format('d/m/Y');
            $ayer = now()->subDay()->format('d/m/Y');
            $query->whereIn('ocurrencias.fecha', [$hoy, $ayer]);
        }

        return $query;
    }

    public function render()
    {
        $ocurrencias = $this->buildQuery()
            ->orderBy('ocurrencias.fecha', 'desc')
            ->orderBy('ocurrencias.id', 'desc')
            ->paginate(10);

        $textFields = ['persona_nombre', 'persona_cargo', 'persona_alias', 'tipo', 'otro', 'detalles', 'observacion', 'vehiculo', 'destino', 'motivo', 'nota_texto'];
        foreach ($ocurrencias as $oc) {
            foreach ($textFields as $f) {
                if (isset($oc->$f)) $oc->$f = t($oc->$f);
            }
        }

        $now = now();
        $mes = $now->month;
        $anio = $now->year;
        $weekStart = $now->copy()->startOfWeek();
        $weekDates = collect(range(0, 6))->map(fn($d) => $weekStart->copy()->addDays($d)->format('d/m/Y'))->toArray();

        return view('livewire.olimpo.ocurrencias-table', [
            'ocurrencias' => $ocurrencias,
            'totalCount' => Ocurrencia::count(),
            'yearCount' => Ocurrencia::where('anio', $anio)->count(),
            'monthCount' => Ocurrencia::where('mes', $mes)->where('anio', $anio)->count(),
            'weekCount' => Ocurrencia::whereIn('fecha', $weekDates)->count(),
        ]);
    }
}
