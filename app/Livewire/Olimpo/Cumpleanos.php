<?php

namespace App\Livewire\Olimpo;

use Livewire\Component;
use App\Models\Cumpleano;
use Carbon\Carbon;

class Cumpleanos extends Component
{
    public $cumpleanos = [];
    public $cumpleanosHoy = [];
    public $selectedId = null;
    public $selectedIds = [];
    public $selectMode = false;
    public $showForm = false;
    public $editId = null;

    public $fecha = '';
    public $nombre = '';
    public $detalles = '';
    public $dni = '';
    public $parentesco = '';
    public $recordatorio_activo = true;
    public $recordatorio_hora = '07:30';
    public $proveedor = 'consultadni';

    public array $proveedores = [
        'consultadni' => 'Simple',
        'kmente' => 'Premium',
    ];

    protected $listeners = ['panelChanged' => 'refreshData', 'importData' => 'handleImport'];

    public function mount()
    {
        $this->refreshData();
    }

    public function refreshData()
    {
        $today = now()->format('d/m');
        $todayMonth = (int) now()->format('m');
        $todayDay = (int) now()->format('d');
        $todayMd = ($todayMonth * 100) + $todayDay;

        $all = Cumpleano::all()->map(function ($c) use ($today, $todayMd, $todayMonth, $todayDay) {
            $dayOfWeek = $this->dayOfWeekForYear($c->fecha);
            $fechaLarga = $this->fechaLarga($c->fecha);
            $proximidad = $this->proximidad($c->fecha, $todayMonth, $todayDay);

            return [
                'id' => $c->id,
                'dni' => $c->dni,
                'fecha' => $c->fecha,
                'fecha_larga' => $fechaLarga,
                'nombre' => $c->nombre,
                'detalles' => $c->detalles,
                'parentesco' => $c->parentesco ?? '',
                'recordatorio_activo' => (bool) $c->recordatorio_activo,
                'recordatorio_hora' => substr($c->recordatorio_hora ?? '07:30:00', 0, 5),
                'dia' => $dayOfWeek,
                'proximidad' => $proximidad,
                'es_hoy' => $c->fecha === $today,
                'es_personal' => false,
            ];
        })->toArray();

        // Personal birthdays merged into the same list
        $existingDnis = collect($all)->pluck('dni')->filter()->values()->toArray();

        $personal = \App\Models\Personal::whereNotNull('fecha_nacimiento')
            ->get()
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
                    'nombre' => $p->nombre,
                    'fecha' => $ddmm,
                    'fecha_larga' => $fechaLarga,
                    'dia' => $dia,
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

        $this->cumpleanos = $merged;
        $this->cumpleanosHoy = array_values(array_filter($merged, fn($c) => $c['es_hoy']));
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
        // Wrap around to next year
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

    public function selectCumpleano($id)
    {
        $this->selectedId = $id;
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
        if (count($this->selectedIds) === count($this->cumpleanos)) {
            $this->selectedIds = [];
        } else {
            $this->selectedIds = collect($this->cumpleanos)->pluck('id')->toArray();
        }
    }

    public function nuevo()
    {
        $this->resetForm();
        $this->showForm = true;
        $this->editId = null;
    }

    public function editar()
    {
        if (!$this->selectedId) {
            $this->dispatch('notify', message: 'Selecciona un cumpleaños primero.', type: 'warning');
            return;
        }
        if (is_string($this->selectedId)) {
            $this->dispatch('notify', message: 'No puedes editar un cumpleaños del personal.', type: 'warning');
            return;
        }
        $c = Cumpleano::find($this->selectedId);
        if (!$c) return;
        $this->fillForm($c);
        $this->showForm = true;
        $this->editId = $c->id;
    }

    public function toggleSelectMode()
    {
        $this->selectMode = !$this->selectMode;
        if (!$this->selectMode) $this->selectedIds = [];
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

    public function eliminar()
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
        if (!$this->selectedId) {
            $this->dispatch('notify', message: 'Selecciona un cumpleaños primero.', type: 'warning');
            return;
        }
        if (is_string($this->selectedId)) {
            $this->dispatch('notify', message: 'No puedes eliminar un cumpleaños del personal.', type: 'warning');
            return;
        }
        $c = Cumpleano::find($this->selectedId);
        if ($c) {
            $c->delete();
            $this->selectedId = null;
            $this->refreshData();
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
        $this->selectedId = null;
        $this->refreshData();
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
        $this->selectedId = null;
        $this->refreshData();
    }

    public function cancel()
    {
        $this->showForm = false;
    }

    public function handleImport($rows)
    {
        $result = \App\Imports\CumpleanosImport::insert($rows ?? []);
        $msg = $result['inserted'] . ' registro(s) importados correctamente.';
        if (!empty($result['errors'])) {
            $msg .= ' Errores: ' . implode(' | ', $result['errors']);
        }
        $this->dispatch('notify', message: $msg, type: empty($result['errors']) ? 'success' : 'warning');
        $this->refreshData();
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
        return view('livewire.olimpo.cumpleanos')
            ->layout('layouts.olimpo', ['title' => 'Cumpleaños']);
    }
}
