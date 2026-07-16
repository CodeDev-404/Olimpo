<?php

namespace App\Livewire\Olimpo;

use Livewire\Component;
use App\Models\Personal as PersonalModel;
use App\Models\Cargo;

class Personal extends Component
{
    public $personal = [];
    public $cargos = [];
    public $selectedId = null;
    public $showForm = false;
    public $editId = null;
    public $search = '';

    public $nombre = '';
    public $segundoNombre = '';
    public $apellidoPaterno = '';
    public $apellidoMaterno = '';
    public $alias = '';
    public $cargoId = '';
    public $departamento = '';
    public $documento = '';
    public $telefono = '';
    public $email = '';
    public $fechaNacimiento = '';
    public $estado = 'ACTIVO';
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

    public function updatedSearch()
    {
        $this->refreshData();
    }

    public function refreshData()
    {
        $deptos = [
            'PRINCIPAL' => 1, 'COMISIONES' => 2,
            'COORDINADOR' => 3, 'SEGURIDAD' => 4,
            'RESIDENCIA' => 5,
            'TORREON' => 6, 'TORREON SUPLENTE' => 6,
        ];

        $query = PersonalModel::with('cargoRel');

        if ($this->search) {
            $q = $this->search;
            $query->where(function ($qry) use ($q) {
                $qry->where('nombre', 'like', "%{$q}%")
                    ->orWhere('cargo', 'like', "%{$q}%")
                    ->orWhere('departamento', 'like', "%{$q}%")
                    ->orWhere('documento', 'like', "%{$q}%");
            });
        }

        $this->personal = $query->get()->toArray();

        foreach ($this->personal as &$p) {
            $fn = $p['fecha_nacimiento'];
            if ($fn) {
                $date = \Carbon\Carbon::parse($fn);
                $p['edad'] = $date->age;
                $p['cumpleaños_format'] = $date->format('d') . ' de ' . ucfirst($date->locale('es')->isoFormat('MMMM')) . ' del ' . $date->format('Y');
            } else {
                $p['edad'] = null;
                $p['cumpleaños_format'] = null;
            }
        }
        unset($p);

        $grupoPri = ['CHOFERES' => 1, 'OLIMPO' => 2, 'COCINA' => 3, 'MANTENIMIENTO' => 4, 'TORREÓN' => 5];

        foreach ($this->personal as &$p) {
            $dep = $p['departamento'] ?? '';
            $p['grupo_rol'] = in_array($dep, ['TORREON', 'TORREON SUPLENTE'])
                ? 'TORREÓN'
                : ($p['cargo_rel']['grupo'] ?? 'OLIMPO');
            $p['orden_rol'] = $p['cargo_rel']['orden'] ?? 0;
        }
        unset($p);

        usort($this->personal, function ($a, $b) use ($deptos, $grupoPri) {
            $rolA = $grupoPri[$a['grupo_rol']] ?? 99;
            $rolB = $grupoPri[$b['grupo_rol']] ?? 99;
            if ($rolA !== $rolB) return $rolA <=> $rolB;

            $depA = $deptos[$a['departamento']] ?? 99;
            $depB = $deptos[$b['departamento']] ?? 99;
            if ($depA !== $depB) return $depA <=> $depB;

            $ordA = $a['orden_rol'] ?? 0;
            $ordB = $b['orden_rol'] ?? 0;
            if ($ordA !== $ordB) return $ordA <=> $ordB;

            return strcasecmp($a['nombre'], $b['nombre']);
        });

        $this->personal = array_values($this->personal);
        $this->cargos = Cargo::orderBy('nombre')->get()->toArray();
    }

    public function selectPersona($id)
    {
        $this->selectedId = $id;
    }

    public function nueva()
    {
        $this->resetForm();
        $this->showForm = true;
        $this->editId = null;
    }

    public function editar()
    {
        if (!$this->selectedId) return;
        $p = PersonalModel::with('cargoRel')->find($this->selectedId);
        if (!$p) return;
        $this->fillForm($p);
        $this->showForm = true;
        $this->editId = $p->id;
    }

    public function save()
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
        $this->validate(['nombre' => 'required']);

        $cargo = $this->cargoId ? Cargo::find($this->cargoId) : null;

        $data = [
            'alias' => $this->alias,
            'nombre' => trim("{$this->nombre} {$this->segundoNombre} {$this->apellidoPaterno} {$this->apellidoMaterno}"),
            'segundo_nombre' => $this->segundoNombre,
            'apellido_paterno' => $this->apellidoPaterno,
            'apellido_materno' => $this->apellidoMaterno,
            'cargo' => $cargo?->nombre ?? '',
            'cargo_id' => $this->cargoId ?: null,
            'departamento' => $this->departamento,
            'documento' => $this->documento,
            'telefono' => $this->telefono,
            'email' => $this->email,
            'fecha_nacimiento' => $this->fechaNacimiento ?: null,
            'estado' => $this->estado,
        ];

        if ($this->editId) {
            $model = PersonalModel::find($this->editId);
            if ($model) $model->update($data);
        } else {
            $model = PersonalModel::create($data);
        }

        if ($this->fechaNacimiento) {
            $fn = \Carbon\Carbon::parse($this->fechaNacimiento);
            $ddmm = $fn->format('d/m');
            $fullName = trim("{$this->nombre} {$this->segundoNombre} {$this->apellidoPaterno} {$this->apellidoMaterno}");

            \App\Models\Cumpleano::updateOrCreate(
                ['dni' => $this->documento ?: null],
                [
                    'fecha' => $ddmm,
                    'nombre' => $fullName,
                    'parentesco' => 'Personal',
                    'recordatorio_activo' => true,
                    'recordatorio_hora' => '07:30:00',
                ]
            );
        } elseif ($this->documento) {
            \App\Models\Cumpleano::where('dni', $this->documento)->delete();
        }

        $this->showForm = false;
        $this->selectedId = null;
        $this->refreshData();
        $this->dispatch('notify', message: 'Personal guardado.', type: 'success');
    }

    public function baja()
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
        if (!$this->selectedId) return;
        $p = PersonalModel::find($this->selectedId);
        if ($p) {
            $p->update(['estado' => 'INACTIVO']);
            $this->selectedId = null;
            $this->refreshData();
            $this->dispatch('notify', message: 'Personal dado de baja.', type: 'success');
        }
    }

    public function eliminar()
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
        if (!$this->selectedId) return;
        $p = PersonalModel::find($this->selectedId);
        if ($p) {
            $p->delete();
            $this->selectedId = null;
            $this->refreshData();
            $this->dispatch('notify', message: 'Personal eliminado permanentemente.', type: 'success');
        }
    }

    public function handleImport($rows)
    {
        $result = \App\Imports\PersonalImport::insert($rows ?? []);
        $msg = $result['inserted'] . ' registro(s) importados correctamente.';
        if (!empty($result['errors'])) {
            $msg .= ' Errores: ' . implode(' | ', $result['errors']);
        }
        $this->dispatch('notify', message: $msg, type: empty($result['errors']) ? 'success' : 'warning');
        $this->refreshData();
    }

    public function consultarDni()
    {
        if (strlen($this->documento) !== 8) {
            $this->dispatch('notify', message: 'El DNI debe tener 8 dígitos.', type: 'warning');
            return;
        }

        try {
            $data = app(\App\Services\DniConsultaService::class)->consultar($this->documento, $this->proveedor);
        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'Error de conexión: ' . $e->getMessage(), type: 'error');
            return;
        }
        if (!$data) {
            $this->dispatch('notify', message: 'DNI no encontrado. Verifica el token y la conexión.', type: 'warning');
            return;
        }
        $nombres = explode(' ', strip_tags($data['nombres'] ?? ''), 2);
        $this->nombre = $nombres[0] ?? '';
        $this->segundoNombre = $nombres[1] ?? '';
        $this->apellidoPaterno = strip_tags($data['apellido_paterno'] ?? '');
        $this->apellidoMaterno = strip_tags($data['apellido_materno'] ?? '');

        if (!empty($data['fecha_nacimiento'])) {
            $this->fechaNacimiento = \Carbon\Carbon::createFromFormat('d/m/Y', $data['fecha_nacimiento'])->format('Y-m-d');
        }

        $this->dispatch('notify', message: 'DNI encontrado: ' . strip_tags($data['nombre_completo'] ?? ''), type: 'success');
    }

    public function cancel() { $this->showForm = false; }

    private function resetForm()
    {
        $this->alias = '';
        $this->nombre = '';
        $this->segundoNombre = '';
        $this->apellidoPaterno = '';
        $this->apellidoMaterno = '';
        $this->cargoId = '';
        $this->departamento = '';
        $this->documento = '';
        $this->telefono = '';
        $this->email = '';
        $this->fechaNacimiento = '';
        $this->estado = 'ACTIVO';
    }

    private function fillForm($p)
    {
        $nameParts = explode(' ', $p->nombre, 3);
        $this->alias = $p->alias;
        $this->nombre = $nameParts[0] ?? '';
        $this->segundoNombre = $p->segundo_nombre;
        $this->apellidoPaterno = $p->apellido_paterno;
        $this->apellidoMaterno = $p->apellido_materno;
        $this->cargoId = (string)$p->cargo_id;
        $this->departamento = $p->departamento;
        $this->documento = $p->documento;
        $this->telefono = $p->telefono;
        $this->email = $p->email;
        $this->fechaNacimiento = $p->fecha_nacimiento;
        $this->estado = $p->estado;
    }

    public function render()
    {
        return view('livewire.olimpo.personal')
            ->layout('layouts.olimpo', ['title' => 'Gestión de Personal']);
    }
}
