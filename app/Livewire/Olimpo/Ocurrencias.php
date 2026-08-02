<?php

namespace App\Livewire\Olimpo;

use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Models\Ocurrencia;
use App\Models\Personal;
use App\Models\TipoOcurrencia;
use App\Models\Cargo;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OcurrenciasExport;

class Ocurrencias extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedId = null;
    public $showForm = false;
    public $editId = null;
    public $refreshKey = 0;

    // Form fields
    public $fecha = '';
    public $hora_ingreso = '';
    public $hora_salida = '';
    public $persona_nombre = '';
    public $personas = [''];
    public $tipo = '';
    public $otro = '';
    public $detalles = '';
    public $observacion = '';
    public $vehiculo = '';
    public $destino = '';
    public $motivo = '';
    public $turno = 'DÍA';

    // Filter fields
    public $filterTurno = '';
    public $filterFecha = '';
    public $filterHoraDesde = '';
    public $filterHoraHasta = '';
    public $filterMesDesde = '';
    public $filterMesHasta = '';

    // Config
    public $configHoraEntradaDia = '08:00';
    public $configHoraSalidaDia = '17:00';
    public $configHoraEntradaNoche = '19:00';
    public $configHoraSalidaNoche = '07:00';

    protected $queryString = ['search' => ['except' => '']];

    protected $listeners = [
        'importData' => 'handleImport',
        'nueva' => 'nueva',
        'editar' => 'editar',
        'duplicar' => 'duplicar',
        'eliminar' => 'eliminar',
        'exportarExcel' => 'exportarExcel',
        'exportarPDF' => 'exportarPDF',
        'exportarCSV' => 'exportarCSV',
    ];

    public function mount()
    {
        $this->fecha = now()->format('d/m/Y');
        $this->search = request('search', '');
        $this->loadConfig();
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

    public function getOcurrenciasQuery()
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
            $query->where(accent_insensitive_search([
                'ocurrencias.persona_nombre',
                'ocurrencias.tipo',
                'ocurrencias.detalles',
                'ocurrencias.otro',
                'ocurrencias.destino',
                'ocurrencias.motivo',
                'ocurrencias.vehiculo',
                'ocurrencias.observacion',
                'ocurrencias.fecha',
                'personal.cargo',
                'personal.alias',
                'personal.documento',
                'personal.telefono',
            ], $this->search));
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

    public function limpiarFiltros()
    {
        $this->search = '';
        $this->filterFecha = '';
        $this->filterHoraDesde = '';
        $this->filterHoraHasta = '';
        $this->filterTurno = '';
        $this->filterMesDesde = '';
        $this->filterMesHasta = '';
    }

    public function updatedSearch() {}
    public function updatedFilterFecha()
    {
        if ($this->filterFecha) {
            $this->filterMesDesde = '';
            $this->filterMesHasta = '';
        }
    }
    public function updatedFilterHoraDesde() {}
    public function updatedFilterHoraHasta() {}
    public function updatedFilterTurno() {}
    public function updatedFilterMesDesde()
    {
        if ($this->filterMesDesde) $this->filterFecha = '';
    }
    public function updatedFilterMesHasta()
    {
        if ($this->filterMesHasta) $this->filterFecha = '';
    }

    public function addPersona()
    {
        $this->personas[] = '';
    }

    public function removePersona($index)
    {
        if (count($this->personas) > 1) {
            unset($this->personas[$index]);
            $this->personas = array_values($this->personas);
        }
    }

    public function setPersonaNombre($index, $value)
    {
        if (isset($this->personas[$index])) {
            $this->personas[$index] = $value;
        }
    }

    public function nueva()
    {
        $this->resetForm();
        $this->fecha = now()->format('d/m/Y');
        $this->turno = $this->detectarTurnoActual();
        $this->showForm = true;
        $this->editId = null;
    }

    public function editar($id = null)
    {
        $id ??= $this->selectedId;
        if (!$id) {
            $this->dispatch('notify', message: 'Selecciona una ocurrencia primero.', type: 'warning');
            return;
        }
        $oc = Ocurrencia::find($id);
        if (!$oc) return;
        $this->fillForm($oc);
        $this->showForm = true;
        $this->editId = $oc->id;
    }

    public function duplicar($id = null)
    {
        $id ??= $this->selectedId;
        if (!$id) {
            $this->dispatch('notify', message: 'Selecciona una ocurrencia primero.', type: 'warning');
            return;
        }
        $oc = Ocurrencia::find($id);
        if (!$oc) return;
        $this->fillForm($oc);
        $this->fecha = now()->format('d/m/Y');
        $this->showForm = true;
        $this->editId = null;
    }

    public function abrirNota()
    {
        $this->dispatch('activate-nota-form')->to('olimpo.ocurrencias-table');
    }

    public function eliminar($id = null)
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
        $id ??= $this->selectedId;
        if (!$id) {
            $this->dispatch('notify', message: 'Selecciona una ocurrencia primero.', type: 'warning');
            return;
        }
        $oc = Ocurrencia::find($id);
        if ($oc) {
            $oc->delete();
            $this->selectedId = null;
            $this->refreshKey++;
            $this->resetPage();
            $this->dispatch('notify', message: 'Ocurrencia eliminada.', type: 'success');
        }
    }

    public function save()
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
        $this->persona_nombre = implode(', ', array_filter($this->personas));

        $this->validate([
            'fecha' => 'required',
            'persona_nombre' => 'required',
        ]);

        try {
            $parts = explode('/', $this->fecha);
            $mes = (int)$parts[1];
            $anio = (int)$parts[2];
        } catch (\Exception $e) {
            $mes = now()->month;
            $anio = now()->year;
        }

        $primerNombre = explode(',', $this->persona_nombre)[0];
        $persona = $primerNombre
            ? \App\Models\Personal::where('nombre', trim($primerNombre))->first()
            : null;

        $tipoModel = $this->tipo
            ? \App\Models\TipoOcurrencia::where('nombre', $this->tipo)->first()
            : null;

        $data = [
            'fecha' => $this->fecha,
            'hora_ingreso' => $this->hora_ingreso,
            'hora_salida' => $this->hora_salida,
            'persona_nombre' => $this->persona_nombre,
            'persona_id' => $persona?->id,
            'tipo' => $this->tipo,
            'tipo_id' => $tipoModel?->id,
            'otro' => $this->otro,
            'detalles' => $this->detalles,
            'observacion' => $this->observacion,
            'vehiculo' => $this->vehiculo,
            'destino' => $this->destino,
            'motivo' => $this->motivo,
            'turno' => $this->turno,
            'mes' => $mes,
            'anio' => $anio,
            'user_id' => auth()->id(),
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
        $this->refreshKey++;
        $this->resetPage();
    }

    public function cancel()
    {
        $this->showForm = false;
        $this->editId = null;
    }

    public function updatedTurno()
    {
        if (!$this->editId) {
            $this->hora_ingreso = $this->turno === 'NOCHE' ? $this->configHoraEntradaNoche : $this->configHoraEntradaDia;
            $this->hora_salida = $this->turno === 'NOCHE' ? $this->configHoraSalidaNoche : $this->configHoraSalidaDia;
        }
    }

    private function resetForm()
    {
        $this->fecha = now()->format('d/m/Y');
        $this->hora_ingreso = '';
        $this->hora_salida = '';
        $this->persona_nombre = '';
        $this->personas = [''];
        $this->tipo = '';
        $this->otro = '';
        $this->detalles = '';
        $this->observacion = '';
        $this->vehiculo = '';
        $this->destino = '';
        $this->motivo = '';
        $this->turno = 'DÍA';
    }

    private function fillForm($oc)
    {
        $this->fecha = $oc->fecha;
        $this->hora_ingreso = $oc->hora_ingreso;
        $this->hora_salida = $oc->hora_salida;
        $this->persona_nombre = $oc->persona_nombre;
        $this->personas = array_map('trim', explode(',', $oc->persona_nombre));
        $this->tipo = $oc->tipo;
        $this->otro = $oc->otro;
        $this->detalles = $oc->detalles;
        $this->observacion = $oc->observacion;
        $this->vehiculo = $oc->vehiculo ?? '';
        $this->destino = $oc->destino ?? '';
        $this->motivo = $oc->motivo ?? '';
        $this->turno = $oc->turno;
    }

    private function getExportRows(): array
    {
        return $this->getOcurrenciasQuery()
            ->orderBy('ocurrencias.fecha', 'desc')
            ->orderBy('ocurrencias.id', 'desc')
            ->get()->toArray();
    }

    public function exportarExcel()
    {
        $rows = $this->getExportRows();
        if (empty($rows)) {
            $this->dispatch('notify', message: 'No hay datos para exportar.', type: 'warning');
            return;
        }
        return Excel::download(new OcurrenciasExport($rows), 'Ocurrencias_' . now()->format('Ymd') . '.xlsx');
    }

    public function exportarPDF()
    {
        $rows = $this->getExportRows();
        if (empty($rows)) {
            $this->dispatch('notify', message: 'No hay datos para exportar.', type: 'warning');
            return;
        }
        $pdf = Pdf::loadView('exports.ocurrencias-pdf', [
            'rows' => $rows,
            'columns' => \App\Exports\OcurrenciasExport::columnMap(),
            'filtro' => 'Exportado desde Ocurrencias',
        ]);
        return response()->streamDownload(fn() => print($pdf->output()), 'Ocurrencias_' . now()->format('Ymd') . '.pdf');
    }

    public function exportarCSV()
    {
        $rows = $this->getExportRows();
        if (empty($rows)) {
            $this->dispatch('notify', message: 'No hay datos para exportar.', type: 'warning');
            return;
        }
        $cols = array_keys(\App\Exports\OcurrenciasExport::columnMap());
        $headers = array_values(\App\Exports\OcurrenciasExport::columnMap());
        $csv = implode(',', $headers) . "\n";
        foreach ($rows as $row) {
            $vals = array_map(fn($c) => '"' . str_replace('"', '""', $row[$c] ?? '') . '"', $cols);
            $csv .= implode(',', $vals) . "\n";
        }
        return response()->streamDownload(fn() => print($csv), 'Ocurrencias_' . now()->format('Ymd') . '.csv', ['Content-Type' => 'text/csv']);
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

    public function getNombresProperty()
    {
        $personal = Personal::activos()->pluck('nombre')->toArray();

        $ocurrencias = Ocurrencia::query()
            ->whereNotNull('persona_nombre')
            ->where('persona_nombre', '!=', '')
            ->pluck('persona_nombre')
            ->flatMap(fn($n) => array_map('trim', explode(',', $n)))
            ->filter()
            ->values()
            ->toArray();

        return array_values(array_unique(array_merge($personal, $ocurrencias)));
    }

    public function getNombresConAliasProperty()
    {
        return Personal::activos()->whereNotNull('alias')->pluck('alias', 'nombre')->toArray();
    }

    public function getDetallesListProperty()
    {
        return Ocurrencia::query()
            ->whereNotNull('detalles')
            ->where('detalles', '!=', '')
            ->pluck('detalles')
            ->unique()
            ->values()
            ->toArray();
    }

    public function getObservacionListProperty()
    {
        return Ocurrencia::query()
            ->whereNotNull('observacion')
            ->where('observacion', '!=', '')
            ->pluck('observacion')
            ->unique()
            ->values()
            ->toArray();
    }

    public function getVehiculoListProperty()
    {
        return Ocurrencia::query()
            ->whereNotNull('vehiculo')
            ->where('vehiculo', '!=', '')
            ->pluck('vehiculo')
            ->unique()
            ->values()
            ->toArray();
    }

    public function getDestinoListProperty()
    {
        return Ocurrencia::query()
            ->whereNotNull('destino')
            ->where('destino', '!=', '')
            ->pluck('destino')
            ->unique()
            ->values()
            ->toArray();
    }

    public function getMotivoListProperty()
    {
        return Ocurrencia::query()
            ->whereNotNull('motivo')
            ->where('motivo', '!=', '')
            ->pluck('motivo')
            ->unique()
            ->values()
            ->toArray();
    }

    public function getTiposProperty()
    {
        return TipoOcurrencia::activos()->pluck('nombre')->toArray();
    }

    public function render()
    {
        return view('livewire.olimpo.ocurrencias', [
            'nombres' => $this->nombres,
            'nombresConAlias' => $this->nombresConAlias,
            'tipos' => $this->tipos,
            'detallesList' => $this->detallesList,
            'observacionList' => $this->observacionList,
            'vehiculoList' => $this->vehiculoList,
            'destinoList' => $this->destinoList,
            'motivoList' => $this->motivoList,
        ])->layout('layouts.olimpo', ['title' => 'Ocurrencias']);
    }
}
