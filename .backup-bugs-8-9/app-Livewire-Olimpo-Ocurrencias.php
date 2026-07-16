<?php

namespace App\Livewire\Olimpo;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Ocurrencia;
use App\Models\Personal;
use App\Models\TipoOcurrencia;
use App\Models\Cargo;

class Ocurrencias extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedId = null;
    public $showForm = false;
    public $editId = null;

    // Form fields
    public $fecha = '';
    public $hora_ingreso = '';
    public $hora_salida = '';
    public $persona_nombre = '';
    public $tipo = '';
    public $otro = '';
    public $detalles = '';
    public $observacion = '';
    public $turno = 'DÍA';

    // Filter fields
    public $filterTurno = '';
    public $filterFecha = '';
    public $filterHoraDesde = '';
    public $filterHoraHasta = '';

    protected $listeners = ['panelChanged' => 'refreshData', 'importData' => 'handleImport'];

    protected $paginationTheme = 'tailwind';

    public function mount()
    {
        $this->fecha = now()->format('d/m/Y');
    }

    public function getOcurrenciasQuery()
    {
        $query = Ocurrencia::query()
            ->leftJoin('personal', 'ocurrencias.persona_nombre', '=', 'personal.nombre')
            ->select('ocurrencias.*', 'personal.cargo as persona_cargo');

        if ($this->filterTurno) {
            $query->where('ocurrencias.turno', $this->filterTurno);
        }

        if ($this->search) {
            $q = $this->search;
            $query->where(function($qry) use ($q) {
                $qry->where('ocurrencias.persona_nombre', 'like', "%{$q}%")
                    ->orWhere('ocurrencias.tipo', 'like', "%{$q}%")
                    ->orWhere('ocurrencias.detalles', 'like', "%{$q}%")
                    ->orWhere('ocurrencias.fecha', 'like', "%{$q}%")
                    ->orWhere('personal.cargo', 'like', "%{$q}%");
            });
        }

        $hasActiveFilter = $this->search || $this->filterFecha
            || $this->filterHoraDesde || $this->filterHoraHasta;

        if (!$hasActiveFilter) {
            $hoy = now()->format('d/m/Y');
            $ayer = now()->subDay()->format('d/m/Y');
            $query->whereIn('ocurrencias.fecha', [$hoy, $ayer]);
        } elseif ($this->filterFecha) {
            $f = \Carbon\Carbon::parse($this->filterFecha);
            $query->where('ocurrencias.fecha', $f->format('d/m/Y'));
        }

        return $query;
    }

    public function limpiarFiltros()
    {
        $this->search = '';
        $this->filterFecha = '';
        $this->filterHoraDesde = '';
        $this->filterHoraHasta = '';
        $this->filterTurno = '';
        $this->resetPage();
    }

    public function updatedSearch() { $this->resetPage(); }
    public function updatedFilterFecha() { $this->resetPage(); }
    public function updatedFilterHoraDesde() { $this->resetPage(); }
    public function updatedFilterHoraHasta() { $this->resetPage(); }
    public function updatedFilterTurno() { $this->resetPage(); }

    public function selectOcurrencia($id)
    {
        $this->selectedId = $id;
    }

    public function nueva()
    {
        $this->resetForm();
        $this->fecha = now()->format('d/m/Y');
        $this->showForm = true;
        $this->editId = null;
    }

    public function editar()
    {
        if (!$this->selectedId) {
            $this->dispatch('notify', message: 'Selecciona una ocurrencia primero.', type: 'warning');
            return;
        }
        $oc = Ocurrencia::find($this->selectedId);
        if (!$oc) return;
        $this->fillForm($oc);
        $this->showForm = true;
        $this->editId = $oc->id;
    }

    public function duplicar()
    {
        if (!$this->selectedId) {
            $this->dispatch('notify', message: 'Selecciona una ocurrencia primero.', type: 'warning');
            return;
        }
        $oc = Ocurrencia::find($this->selectedId);
        if (!$oc) return;
        $this->fillForm($oc);
        $this->fecha = now()->format('d/m/Y');
        $this->showForm = true;
        $this->editId = null;
    }

    public function eliminar()
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
        if (!$this->selectedId) {
            $this->dispatch('notify', message: 'Selecciona una ocurrencia primero.', type: 'warning');
            return;
        }
        $oc = Ocurrencia::find($this->selectedId);
        if ($oc) {
            $oc->delete();
            $this->selectedId = null;
            $this->resetPage();
            $this->dispatch('notify', message: 'Ocurrencia eliminada.', type: 'success');
        }
    }

    public function save()
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
        $this->validate([
            'fecha' => 'required',
            'persona_nombre' => 'required',
            'tipo' => 'required',
        ]);

        try {
            $parts = explode('/', $this->fecha);
            $mes = (int)$parts[1];
            $anio = (int)$parts[2];
        } catch (\Exception $e) {
            $mes = now()->month;
            $anio = now()->year;
        }

        $data = [
            'fecha' => $this->fecha,
            'hora_ingreso' => $this->hora_ingreso,
            'hora_salida' => $this->hora_salida,
            'persona_nombre' => $this->persona_nombre,
            'tipo' => $this->tipo,
            'otro' => $this->otro,
            'detalles' => $this->detalles,
            'observacion' => $this->observacion,
            'turno' => $this->turno,
            'mes' => $mes,
            'anio' => $anio,
        ];

        if ($this->editId) {
            $ocurrencia = Ocurrencia::find($this->editId);
            if ($ocurrencia) $ocurrencia->update($data);
            $this->dispatch('notify', message: 'Ocurrencia actualizada.', type: 'success');
        } else {
            Ocurrencia::create($data);
            $this->dispatch('notify', message: 'Ocurrencia registrada.', type: 'success');
        }

        $this->showForm = false;
        $this->selectedId = null;
        $this->resetPage();
    }

    public function cancel()
    {
        $this->showForm = false;
        $this->editId = null;
    }

    private function resetForm()
    {
        $this->fecha = now()->format('d/m/Y');
        $this->hora_ingreso = '';
        $this->hora_salida = '';
        $this->persona_nombre = '';
        $this->tipo = '';
        $this->otro = '';
        $this->detalles = '';
        $this->observacion = '';
        $this->turno = 'DÍA';
    }

    private function fillForm($oc)
    {
        $this->fecha = $oc->fecha;
        $this->hora_ingreso = $oc->hora_ingreso;
        $this->hora_salida = $oc->hora_salida;
        $this->persona_nombre = $oc->persona_nombre;
        $this->tipo = $oc->tipo;
        $this->otro = $oc->otro;
        $this->detalles = $oc->detalles;
        $this->observacion = $oc->observacion;
        $this->turno = $oc->turno;
    }

    public function handleImport($rows)
    {
        $result = \App\Imports\OcurrenciasImport::insert($rows ?? []);
        $msg = $result['inserted'] . ' registro(s) importados correctamente.';
        if (!empty($result['errors'])) {
            $msg .= ' Errores: ' . implode(' | ', $result['errors']);
        }
        $this->dispatch('notify', message: $msg, type: empty($result['errors']) ? 'success' : 'warning');
        $this->resetPage();
    }

    public function tipoColor($tipo)
    {
        $t = TipoOcurrencia::where('nombre', $tipo)->first();
        return $t ? $t->color : '#888899';
    }

    public function getNombresProperty()
    {
        return Personal::activos()->pluck('nombre')->toArray();
    }

    public function getTiposProperty()
    {
        return TipoOcurrencia::activos()->pluck('nombre')->toArray();
    }

    public function render()
    {
        $query = $this->getOcurrenciasQuery();

        if ($this->filterHoraDesde || $this->filterHoraHasta) {
            $rows = $query->get();
            $rows = $rows->filter(function($r) {
                $h = $r->hora_ingreso;
                if (!$h) return true;
                try {
                    $parts = explode(':', $h);
                    $mins = (int)$parts[0] * 60 + (int)$parts[1];
                    $desde = $this->filterHoraDesde ? (int)explode(':', $this->filterHoraDesde)[0] * 60 + (int)explode(':', $this->filterHoraDesde)[1] : 0;
                    $hasta = $this->filterHoraHasta ? (int)explode(':', $this->filterHoraHasta)[0] * 60 + (int)explode(':', $this->filterHoraHasta)[1] : 1440;
                    return $mins >= $desde && $mins <= $hasta;
                } catch (\Exception $e) {
                    return true;
                }
            })->sortByDesc('fecha')->values();
            $ocurrencias = $rows;
        } else {
            $ocurrencias = $query->orderBy('ocurrencias.fecha', 'desc')
                ->orderBy('ocurrencias.id', 'desc')
                ->paginate(25);
        }

        return view('livewire.olimpo.ocurrencias', [
            'nombres' => $this->nombres,
            'tipos' => $this->tipos,
            'ocurrencias' => $ocurrencias,
        ])->layout('layouts.olimpo', ['title' => 'Ocurrencias']);
    }
}