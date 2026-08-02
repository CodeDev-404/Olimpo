<?php

namespace App\Livewire\Olimpo;

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\Cumpleano;
use App\Models\Personal;
use App\Models\RecordatorioProgramado;
use Carbon\Carbon;

class Cumpleanos extends Component
{
    public $cumpleanosHoy = [];
    public $programadosHoy = [];
    public $showForm = false;
    public $editId = null;
    public $selectMode = false;
    public $selectedIds = [];
    public $selectedId = null;

    public $showProgramar = false;
    public $programarId = null;
    public $programarCumpleanoId = null;
    public $programarNombre = '';
    public $programarFecha = '';
    public $programarHora = '07:30';
    public $programarLimite = '';

    public $fecha = '';
    public $nombre = '';
    public $detalles = '';
    public $dni = '';
    public $parentesco = '';
    public $recordatorio_activo = true;
    public $recordatorio_hora = '07:30';
    public $proveedor = 'consultadni';
    public $search = '';
    public $filterRecordatorio = '';
    public $filterProximidad = '';

    public array $proveedores = [
        'consultadni' => 'Simple',
        'kmente' => 'Premium',
    ];

    protected $listeners = [
        'importData' => 'handleImport',
        'editar' => 'editar',
        'eliminar' => 'eliminar',
        'programar' => 'programarRecordatorio',
        'marcarEnviado' => 'marcarEnviado',
    ];

    public function mount()
    {
        $this->refreshCumpleanosHoy();
        $this->refreshProgramadosHoy();
    }

    public function refreshProgramadosHoy()
    {
        $this->programadosHoy = RecordatorioProgramado::whereDate('fecha', now()->toDateString())
            ->where('enviado', false)
            ->get()
            ->map(fn($r) => [
                'id' => $r->id,
                'hora' => substr($r->hora, 0, 5),
                'nombre' => $r->cumpleano?->nombre ?? '',
                'parentesco' => $r->cumpleano?->parentesco ?? '',
            ])
            ->values()
            ->toArray();
    }

    public function programarRecordatorio($id = null)
    {
        $id = is_array($id) ? ($id['id'] ?? null) : $id;
        if (is_string($id)) {
            $this->dispatch('notify', message: 'No puedes programar un recordatorio de un cumpleaños del personal.', type: 'warning');
            return;
        }
        $c = Cumpleano::find($id);
        if (!$c) return;

        $this->programarCumpleanoId = $c->id;
        $this->programarNombre = $c->nombre;
        [$d, $m] = array_map('intval', explode('/', $c->fecha));
        $fechaCumple = Carbon::create(now()->year, $m, $d);
        if ($fechaCumple->lt(now()->startOfDay())) $fechaCumple->addYear();
        $this->programarLimite = $fechaCumple->format('d/m/Y');

        $existing = RecordatorioProgramado::where('cumpleano_id', $c->id)->latest()->first();
        $this->programarId = $existing?->id;
        $this->programarFecha = $existing?->fecha?->format('Y-m-d') ?? '';
        $this->programarHora = $existing ? substr($existing->hora, 0, 5) : '07:30';

        $this->showProgramar = true;
    }

    public function guardarProgramado()
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
        $this->validate([
            'programarFecha' => 'required|date',
            'programarHora' => 'required|date_format:H:i',
        ], [
            'programarFecha.required' => 'La fecha es obligatoria.',
            'programarHora.required' => 'La hora es obligatoria.',
            'programarHora.date_format' => 'La hora debe tener formato HH:MM.',
        ]);

        $limite = Carbon::createFromFormat('d/m/Y', $this->programarLimite)->startOfDay();
        $fecha = Carbon::parse($this->programarFecha)->startOfDay();
        if ($fecha->lt(now()->startOfDay())) {
            $this->dispatch('notify', message: 'La fecha debe ser hoy o una fecha futura.', type: 'warning');
            return;
        }
        if ($fecha->gte($limite)) {
            $this->dispatch('notify', message: "La fecha debe ser antes del cumpleaños ({$this->programarLimite}).", type: 'warning');
            return;
        }

        RecordatorioProgramado::updateOrCreate(
            ['id' => $this->programarId],
            [
                'cumpleano_id' => $this->programarCumpleanoId,
                'fecha' => $fecha->format('Y-m-d'),
                'hora' => $this->programarHora . ':00',
                'enviado' => false,
            ]
        );

        $this->showProgramar = false;
        $this->refreshProgramadosHoy();
        $this->dispatch('notify', message: 'Recordatorio programado.', type: 'success');
    }

    public function eliminarProgramado()
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
        if ($this->programarId) {
            RecordatorioProgramado::find($this->programarId)?->delete();
        }
        $this->showProgramar = false;
        $this->refreshProgramadosHoy();
        $this->dispatch('notify', message: 'Recordatorio programado eliminado.', type: 'success');
    }

    public function cancelarProgramado()
    {
        $this->showProgramar = false;
        $this->programarId = null;
    }

    public function marcarEnviado($id)
    {
        RecordatorioProgramado::whereKey($id)->update(['enviado' => true]);
        $this->refreshProgramadosHoy();
    }

    #[Computed]
    public function getCumpleanosProperty()
    {
        return $this->filterCumpleanos(
            cache()->remember('cumpleanos_list', 3600, function () {
            $today = now()->format('d/m');
            $todayMonth = (int) now()->format('m');
            $todayDay = (int) now()->format('d');
            $todayMd = ($todayMonth * 100) + $todayDay;

            $aliases = [];
            $dnis = Cumpleano::whereNotNull('dni')->pluck('dni');
            if ($dnis->isNotEmpty()) {
                $aliases = Personal::whereIn('documento', $dnis->toArray())
                    ->pluck('alias', 'documento')
                    ->toArray();
            }

            $all = Cumpleano::all()->map(function ($c) use ($today, $todayMd, $todayMonth, $todayDay, $aliases) {
                $dayOfWeek = $this->dayOfWeekForYear($c->fecha);
                $fechaLarga = $this->fechaLarga($c->fecha);
                $proximidad = $this->proximidad($c->fecha, $todayMonth, $todayDay);
                $alias = $c->dni ? ($aliases[$c->dni] ?? null) : null;

                return [
                    'id' => $c->id,
                    'dni' => $c->dni,
                    'alias' => t($alias),
                    'fecha' => $c->fecha,
                    'fecha_larga' => $fechaLarga,
                    'nombre' => t($c->nombre),
                    'detalles' => t($c->detalles),
                    'parentesco' => t($c->parentesco ?? ''),
                    'recordatorio_activo' => (bool) $c->recordatorio_activo,
                    'recordatorio_hora' => substr($c->recordatorio_hora ?? '07:30:00', 0, 5),
                    'dia' => t($dayOfWeek),
                    'proximidad' => $proximidad,
                    'es_hoy' => $c->fecha === $today,
                    'es_personal' => false,
                ];
            })->toArray();

            $existingDnis = collect($all)->pluck('dni')->filter()->values()->toArray();

            $personal = Personal::whereNotNull('fecha_nacimiento')
                ->get(['id', 'nombre', 'alias', 'documento', 'fecha_nacimiento'])
                ->reject(fn($p) => $p->documento && in_array($p->documento, $existingDnis))
                ->map(function ($p) use ($today, $todayMd, $todayMonth, $todayDay) {
                    $fn = Carbon::parse($p->fecha_nacimiento);
                    $ddmm = $fn->format('d/m');
                    $fechaLarga = $fn->format('d') . ' de ' . ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'][(int)$fn->format('m') - 1];
                    $dia = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'][$fn->dayOfWeek];

                    $md = ((int)$fn->format('m') * 100) + (int)$fn->format('d');
                    $proximidad = $md >= $todayMd ? $md - $todayMd : (1231 - $todayMd) + $md;

                    return [
                        'id' => 'p_' . $p->id,
                        'alias' => t($p->alias),
                        'nombre' => t($p->nombre),
                        'fecha' => $ddmm,
                        'fecha_larga' => $fechaLarga,
                        'dia' => t($dia),
                        'proximidad' => $proximidad,
                        'es_hoy' => $ddmm === $today,
                        'es_personal' => true,
                        'parentesco' => '',
                        'detalles' => '',
                        'recordatorio_activo' => false,
                        'recordatorio_hora' => '',
                    ];
                })
                ->toArray();

            $merged = array_merge($all, $personal);
            usort($merged, fn($a, $b) => $a['proximidad'] <=> $b['proximidad']);

            return $merged;
        })
        );
    }

    private function filterCumpleanos(array $rows): array
    {
        $rows = array_filter($rows, function ($c) {
            if ($this->filterRecordatorio === 'activos' && empty($c['recordatorio_activo'])) return false;
            if ($this->filterRecordatorio === 'inactivos' && !empty($c['recordatorio_activo'])) return false;
            return true;
        });
        if ($this->filterProximidad !== '') {
            $max = (int) $this->filterProximidad;
            $rows = array_filter($rows, fn($c) => $c['proximidad'] <= $max);
        }
        if ($this->search !== '') {
            $term = unaccent_string(mb_strtolower(trim($this->search)));
            $rows = array_filter($rows, function ($c) use ($term) {
                $haystack = unaccent_string(mb_strtolower(implode(' ', [
                    $c['nombre'] ?? '', $c['alias'] ?? '', $c['parentesco'] ?? '', $c['detalles'] ?? '',
                ])));
                return str_contains($haystack, $term);
            });
        }
        return array_values($rows);
    }

    public function limpiarFiltros()
    {
        $this->search = '';
        $this->filterRecordatorio = '';
        $this->filterProximidad = '';
    }

    public function refreshCumpleanosHoy()
    {
        $all = $this->cumpleanos;
        $this->cumpleanosHoy = array_values(array_filter($all, fn($c) => $c['es_hoy']));
    }

    public function selectCumpleano($id)
    {
        $this->selectedId = $id;
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
        $this->selectedIds = array_values($this->selectedIds);
    }

    public function toggleSelectAll()
    {
        $ids = array_map(fn($c) => (int)($c['id'] ?? 0), $this->cumpleanos);
        $ids = array_values(array_filter($ids));
        $current = array_values(array_unique(array_map('intval', $this->selectedIds)));
        $allSelected = $ids && !array_diff($ids, $current);
        if ($allSelected) {
            $this->selectedIds = array_values(array_diff($current, $ids));
        } else {
            $this->selectedIds = array_values(array_unique(array_merge($current, $ids)));
        }
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

    public function nuevo()
    {
        $this->resetForm();
        $this->showForm = true;
        $this->editId = null;
    }

    public function editar($id = null)
    {
        $id ??= $this->selectedId;
        if (!$id) {
            $this->dispatch('notify', message: 'Selecciona un cumpleaños primero.', type: 'warning');
            return;
        }
        if (is_string($id)) {
            $this->dispatch('notify', message: 'No puedes editar un cumpleaños del personal.', type: 'warning');
            return;
        }
        $c = Cumpleano::find($id);
        if (!$c) return;
        $this->fillForm($c);
        $this->showForm = true;
        $this->editId = $c->id;
    }

    public function consultarDni()
    {
        if (strlen($this->dni) !== 8) {
            $this->dispatch('notify', message: 'El DNI debe tener 8 dígitos.', type: 'warning');
            return;
        }
        try {
            $data = app(\App\Services\DniConsultaService::class)->consultar($this->dni, $this->proveedor);
            if (!$data) {
                $this->dispatch('notify', message: 'DNI no encontrado. Verifica el token y la conexión.', type: 'warning');
                return;
            }
            $this->nombre = ucwords(strtolower(strip_tags($data['nombres'] . ' ' . $data['apellido_paterno'] . ' ' . $data['apellido_materno'])));

            if (!empty($data['fecha_nacimiento'])) {
                $this->fecha = substr($data['fecha_nacimiento'], 0, 5);
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'Error de conexión: ' . $e->getMessage(), type: 'danger');
        }
    }

    public function eliminar($id = null)
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
        $id ??= $this->selectedId;
        if (!$id) {
            $this->dispatch('notify', message: 'Selecciona un cumpleaños primero.', type: 'warning');
            return;
        }
        if (is_string($id)) {
            $this->dispatch('notify', message: 'No puedes eliminar un cumpleaños del personal.', type: 'warning');
            return;
        }
        $c = Cumpleano::find($id);
        if ($c) {
            $c->delete();
            cache()->forget('cumpleanos_list');
            $this->refreshCumpleanosHoy();
            $this->dispatch('notify', message: 'Cumpleaños eliminado.', type: 'success');
        }
    }

    public function eliminarSeleccionados()
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
        $ids = array_filter($this->selectedIds, fn($id) => !is_string($id));
        if (empty($ids)) {
            $this->dispatch('notify', message: 'Selecciona uno o más cumpleaños.', type: 'warning');
            return;
        }
        $count = Cumpleano::whereIn('id', $ids)->delete();
        $this->selectedIds = [];
        cache()->forget('cumpleanos_list');
        $this->refreshCumpleanosHoy();
        $this->dispatch('notify', message: $count . ' cumpleaño(s) eliminado(s).', type: 'success');
    }

    public function save()
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
        $rules = [
            'fecha' => 'required|regex:/^\d{2}\/\d{2}$/',
            'nombre' => 'required',
            'dni' => 'nullable|digits:8',
        ];
        if ($this->recordatorio_activo) {
            $rules['recordatorio_hora'] = 'required|date_format:H:i';
        }

        $this->validate($rules);

        $horaRecordatorio = $this->recordatorio_activo && $this->recordatorio_hora
            ? $this->recordatorio_hora . ':00'
            : '07:30:00';

        $data = [
            'fecha' => $this->fecha,
            'nombre' => ucwords(strtolower($this->nombre)),
            'detalles' => $this->detalles ? ucwords(strtolower($this->detalles)) : '',
            'parentesco' => $this->parentesco,
            'dni' => $this->dni,
            'recordatorio_activo' => $this->recordatorio_activo,
            'recordatorio_hora' => $horaRecordatorio,
        ];

        if ($this->editId) {
            $cumpleano = Cumpleano::find($this->editId);
            if ($cumpleano) $cumpleano->update($data);
            $this->dispatch('notify', message: 'Cumpleaños actualizado.', type: 'success');
        } else {
            Cumpleano::create($data);
            $this->dispatch('notify', message: 'Cumpleaños registrado.', type: 'success');
        }

        $this->showForm = false;
        $this->dispatch('close-form-modal');
        cache()->forget('cumpleanos_list');
        $this->refreshCumpleanosHoy();
    }

    public function cancel()
    {
        $this->showForm = false;
    }

    public function handleImport($rows, $panel = null)
    {
        $result = \App\Imports\CumpleanosImport::insert($rows ?? []);
        $msg = $result['inserted'] . ' registro(s) importados correctamente.';
        if (!empty($result['errors'])) {
            $msg .= ' Errores: ' . implode(' | ', $result['errors']);
        }
        $this->dispatch('notify', message: $msg, type: empty($result['errors']) ? 'success' : 'warning');
        cache()->forget('cumpleanos_list');
        $this->refreshCumpleanosHoy();
    }

    private function resetForm()
    {
        $this->fecha = '';
        $this->nombre = '';
        $this->detalles = '';
        $this->dni = '';
        $this->parentesco = '';
        $this->recordatorio_activo = true;
        $this->recordatorio_hora = '07:30';
    }

    private function fillForm($c)
    {
        $this->fecha = $c->fecha;
        $this->nombre = $c->nombre;
        $this->detalles = $c->detalles;
        $this->parentesco = $c->parentesco ?? '';
        $this->recordatorio_activo = (bool)($c->recordatorio_activo ?? false);
        $hora = $c->recordatorio_hora ?? '07:30:00';
        $this->recordatorio_hora = substr($hora, 0, 5);
    }

    public function render()
    {
        $cumpleanos = $this->cumpleanos;
        $this->cumpleanosHoy = array_values(array_filter($cumpleanos, fn($c) => !empty($c['es_hoy'])));
        $proximos = array_values(array_filter($cumpleanos, fn($c) => empty($c['es_hoy'])));

        return view('livewire.olimpo.cumpleanos', [
            'cumpleanosHoy' => $this->cumpleanosHoy,
            'countHoy' => count(array_filter($cumpleanos, fn($c) => !empty($c['es_hoy']))),
            'count7' => count(array_filter($proximos, fn($c) => $c['proximidad'] <= 7)),
            'count30' => count(array_filter($proximos, fn($c) => $c['proximidad'] <= 30)),
            'total' => count($cumpleanos),
            'activeFilters' => ($this->search !== '' ? 1 : 0) + ($this->filterProximidad !== '' ? 1 : 0) + ($this->filterRecordatorio !== '' ? 1 : 0),
        ])->layout('layouts.olimpo', ['title' => 'Cumpleaños']);
    }
}
