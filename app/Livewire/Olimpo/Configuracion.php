<?php

namespace App\Livewire\Olimpo;

use Livewire\Component;
use App\Models\TipoOcurrencia;
use App\Models\Cargo;
use App\Models\Camioneta;
use App\Models\Personal;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class Configuracion extends Component
{
    public $isAuth = false;
    public $activeTab = 'tipos';

    // Tipo form
    public $tipoId = null;
    public $tipoNombre = '';
    public $tipoNivel = '';
    public $tipoColor = '#F0C040';
    public $tipoActivo = true;
    public $showTipoForm = false;

    // Cargo form
    public $cargoNombre = '';
    public $cargoGrupo = 'OLIMPO';
    public $cargoOrden = 0;
    public $showCargoForm = false;
    public $cargoEditId = null;

    // Camioneta form
    public $camId = null;
    public $camPlaca = '';
    public $camMarca = '';
    public $camModelo = '';
    public $camAnio = '';
    public $camColor = '';
    public $showCamForm = false;

    // User form
    public $userId = null;
    public $userUsername = '';
    public $userPassword = '';
    public $userName = '';
    public $userRole = 'user';
    public $showUserForm = false;

    // Settings
    public $horaEntradaDia = '08:00';
    public $horaSalidaDia = '17:00';
    public $horaEntradaNoche = '19:00';
    public $horaSalidaNoche = '07:00';
    public $limiteBuenoMin = 5;
    public $limiteRegularMin = 20;

    public $tipos = [];
    public $cargos = [];
    public $camionetas = [];
    public $usuarios = [];

    protected $listeners = [];

    public $niveles = ['Leve', 'Moderado', 'Grave', 'Crítico'];

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function mount()
    {
        $this->checkAuth();
        $this->loadSettings();
        $this->refreshData();
    }

    public function checkAuth()
    {
        $this->isAuth = auth()->user()?->role === 'admin';
    }

    public function loadSettings()
    {
        $config = DB::table('configuracion')->get()->keyBy('clave');
        $this->horaEntradaDia = $config['hora_entrada_dia']->valor ?? '08:00';
        $this->horaSalidaDia = $config['hora_salida_dia']->valor ?? '17:00';
        $this->horaEntradaNoche = $config['hora_entrada_noche']->valor ?? '19:00';
        $this->horaSalidaNoche = $config['hora_salida_noche']->valor ?? '07:00';
        $this->limiteBuenoMin = (int)($config['limite_bueno_min']->valor ?? 5);
        $this->limiteRegularMin = (int)($config['limite_regular_min']->valor ?? 20);
    }

    public function refreshData()
    {
        $this->tipos = TipoOcurrencia::orderBy('nombre')->get()->toArray();
        $this->cargos = Cargo::orderBy('nombre')->get()->toArray();
        $this->camionetas = Camioneta::orderBy('placa')->get()->toArray();
        $this->usuarios = User::orderBy('name')->get()->toArray();
    }

    public function saveSettings()
    {
        $this->checkAuth();
        $data = [
            'hora_entrada_dia' => $this->horaEntradaDia,
            'hora_salida_dia' => $this->horaSalidaDia,
            'hora_entrada_noche' => $this->horaEntradaNoche,
            'hora_salida_noche' => $this->horaSalidaNoche,
            'limite_bueno_min' => $this->limiteBuenoMin,
            'limite_regular_min' => $this->limiteRegularMin,
        ];
        foreach ($data as $key => $val) {
            DB::table('configuracion')->updateOrInsert(['clave' => $key], ['valor' => $val]);
        }
        $this->dispatch('notify', message: 'Configuración guardada.', type: 'success');
    }

    // Tipo CRUD
    public function newTipo()
    {
        $this->tipoId = null;
        $this->tipoNombre = '';
        $this->tipoNivel = '';
        $this->tipoColor = '#F0C040';
        $this->tipoActivo = true;
        $this->showTipoForm = true;
    }

    public function editTipo($id)
    {
        $t = TipoOcurrencia::find($id);
        if (!$t) return;
        $this->tipoId = $t->id;
        $this->tipoNombre = $t->nombre;
        $this->tipoNivel = $t->nivel;
        $this->tipoColor = $t->color;
        $this->tipoActivo = $t->activo;
        $this->showTipoForm = true;
    }

    public function saveTipo()
    {
        $this->checkAuth();
        $this->validate(['tipoNombre' => 'required']);
        $data = ['nombre' => $this->tipoNombre, 'nivel' => $this->tipoNivel, 'color' => $this->tipoColor, 'activo' => $this->tipoActivo];
        if ($this->tipoId) {
            $tipo = TipoOcurrencia::find($this->tipoId);
            if ($tipo) $tipo->update($data);
        } else {
            TipoOcurrencia::create($data);
        }
        $this->showTipoForm = false;
        $this->refreshData();
        $this->dispatch('notify', message: 'Tipo guardado.', type: 'success');
    }

    public function deleteTipo($id)
    {
        $this->checkAuth();
        TipoOcurrencia::find($id)?->delete();
        $this->refreshData();
        $this->dispatch('notify', message: 'Tipo eliminado.', type: 'success');
    }

    // Cargo CRUD
    public function newCargo()
    {
        $this->cargoNombre = '';
        $this->cargoGrupo = 'OLIMPO';
        $this->cargoOrden = 0;
        $this->cargoEditId = null;
        $this->showCargoForm = true;
    }

    public function editCargo($id)
    {
        $c = Cargo::find($id);
        if (!$c) return;
        $this->cargoNombre = $c->nombre;
        $this->cargoGrupo = $c->grupo;
        $this->cargoOrden = $c->orden;
        $this->cargoEditId = $c->id;
        $this->showCargoForm = true;
    }

    public function saveCargo()
    {
        $this->checkAuth();
        $this->validate(['cargoNombre' => 'required']);
        $data = ['nombre' => $this->cargoNombre, 'grupo' => $this->cargoGrupo, 'orden' => $this->cargoOrden];
        if ($this->cargoEditId) {
            $cargo = Cargo::find($this->cargoEditId);
            if ($cargo) $cargo->update($data);
        } else {
            Cargo::create($data);
        }
        $this->showCargoForm = false;
        $this->refreshData();
        $this->dispatch('notify', message: 'Cargo guardado.', type: 'success');
    }

    public function deleteCargo($id)
    {
        $this->checkAuth();
        Cargo::find($id)?->delete();
        $this->refreshData();
        $this->dispatch('notify', message: 'Cargo eliminado.', type: 'success');
    }

    // Camioneta CRUD
    public function newCamioneta()
    {
        $this->resetCamForm();
        $this->showCamForm = true;
        $this->camId = null;
    }

    public function editCamioneta($id)
    {
        $c = Camioneta::find($id);
        if (!$c) return;
        $this->camId = $c->id;
        $this->camPlaca = $c->placa;
        $this->camMarca = $c->marca;
        $this->camModelo = $c->modelo;
        $this->camAnio = $c->anio;
        $this->camColor = $c->color;
        $this->showCamForm = true;
    }

    public function saveCamioneta()
    {
        $this->checkAuth();
        $this->validate(['camPlaca' => 'required']);
        $data = ['placa' => $this->camPlaca, 'marca' => $this->camMarca, 'modelo' => $this->camModelo, 'anio' => $this->camAnio, 'color' => $this->camColor];
        if ($this->camId) {
            $camioneta = Camioneta::find($this->camId);
            if ($camioneta) $camioneta->update($data);
        } else {
            Camioneta::create($data);
        }
        $this->showCamForm = false;
        $this->refreshData();
        $this->dispatch('notify', message: 'Camioneta guardada.', type: 'success');
    }

    public function deleteCamioneta($id)
    {
        $this->checkAuth();
        Camioneta::find($id)?->delete();
        $this->refreshData();
        $this->dispatch('notify', message: 'Camioneta eliminada.', type: 'success');
    }

    private function resetCamForm()
    {
        $this->camPlaca = '';
        $this->camMarca = '';
        $this->camModelo = '';
        $this->camAnio = '';
        $this->camColor = '';
    }

    // User CRUD
    public function newUser()
    {
        $this->userId = null;
        $this->userUsername = '';
        $this->userPassword = '';
        $this->userName = '';
        $this->userRole = 'user';
        $this->showUserForm = true;
    }

    public function saveUser()
    {
        $this->checkAuth();
        $this->validate(['userUsername' => 'required']);
        $data = ['name' => $this->userName ?: $this->userUsername, 'email' => $this->userUsername . '@olimpo.com', 'role' => $this->userRole];
        if ($this->userPassword) {
            $data['password'] = Hash::make($this->userPassword);
        }
        if ($this->userId) {
            $user = User::find($this->userId);
            if ($user) $user->update($data);
        } else {
            if (!$this->userPassword) {
                $this->dispatch('notify', message: 'La contraseña es obligatoria para nuevos usuarios.', type: 'error');
                return;
            }
            $data['password'] = Hash::make($this->userPassword);
            User::create($data);
        }
        $this->showUserForm = false;
        $this->refreshData();
        $this->dispatch('notify', message: 'Usuario guardado.', type: 'success');
    }

    public function deleteUser($id)
    {
        $this->checkAuth();
        $adminCount = User::where('role', 'admin')->count();
        $user = User::find($id);
        if ($user && $user->role === 'admin' && $adminCount <= 1) {
            $this->dispatch('notify', message: 'Debe haber al menos un administrador.', type: 'error');
            return;
        }
        $user?->delete();
        $this->refreshData();
        $this->dispatch('notify', message: 'Usuario eliminado.', type: 'success');
    }

    public function render()
    {
        return view('livewire.olimpo.configuracion')
            ->layout('layouts.olimpo', ['title' => 'Configuración']);
    }
}
