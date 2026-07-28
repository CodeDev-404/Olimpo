<?php

namespace App\Livewire\Olimpo;

use App\Models\ConsultaHistorial;
use App\Services\DniConsultaService;
use Livewire\Component;

class ConsultasDni extends Component
{
    public string $documento = '';

    public string $tipo = 'dni';

    public string $herramienta = 'consultadni';

    public string $modo = 'simple';

    protected ?array $resultado = null;

    public array $historial = [];

    public bool $showModal = false;

    public string $modalTitle = '';

    public string $searchTerm = '';

    public array $herramientas = [];

    public array $premiumHerramientas = [];

    private int $historialLimit = 10;

    public function mount()
    {
        if (auth()->user()?->role !== 'admin') {
            abort(403);
        }
        $this->herramientas = $this->getHerramientas();
        $this->premiumHerramientas = collect($this->herramientas)
            ->where('id', '!=', 'consultadni')
            ->sortBy(function ($item) {
                if ($item['id'] === 'kmente') return '0';
                if ($item['id'] === 'busqueda-nombres') return '1';
                return $item['label'];
            })
            ->values()
            ->toArray();
        $this->resultado = null;
        $this->showModal = false;
        $this->cargarHistorial();
    }

    public function cambiarModo(string $modo)
    {
        $this->modo = $modo;
        if ($modo === 'simple') {
            $this->seleccionarHerramienta('consultadni');
        } else {
            $first = $this->premiumHerramientas[0] ?? null;
            if ($first) {
                $this->seleccionarHerramienta($first['id']);
            }
        }
    }

    public function cargarHistorial()
    {
        $this->historial = ConsultaHistorial::where('user_id', auth()->id())
            ->latest()
            ->take($this->historialLimit)
            ->get(['id', 'tipo', 'documento', 'nombre_mostrar', 'created_at'])
            ->toArray();
    }

    public function getHerramientas(): array
    {
        return [
            ['id' => 'consultadni', 'label' => 'Simple', 'input' => 'dni', 'color' => 'bg-slate-500', 'group' => 'Rápido'],
            ['id' => 'kmente', 'label' => 'Búsqueda por DNI', 'input' => 'dni', 'color' => 'bg-amber-500', 'group' => 'Completo'],
            ['id' => 'telefonos', 'label' => 'Teléfonos', 'input' => 'dni', 'color' => 'bg-green-500', 'group' => 'Completo'],
            ['id' => 'sunarp', 'label' => 'Sunarp', 'input' => 'dni', 'color' => 'bg-indigo-500', 'group' => 'Completo'],
            ['id' => 'reniec', 'label' => 'Reniec', 'input' => 'dni', 'color' => 'bg-blue-500', 'group' => 'Completo'],
            ['id' => 'ficha-reniec', 'label' => 'Ficha Reniec', 'input' => 'dni', 'color' => 'bg-blue-600', 'group' => 'Completo'],
            ['id' => 'busqueda-nombres', 'label' => 'Búsqueda por nombres', 'input' => 'name', 'color' => 'bg-cyan-500', 'group' => 'Completo'],
            ['id' => 'dni-virtual', 'label' => 'DNI Virtual', 'input' => 'dni', 'color' => 'bg-teal-500', 'group' => 'Completo'],
            ['id' => 'arbol-genealogico', 'label' => 'Árbol genealógico', 'input' => 'dni', 'color' => 'bg-emerald-500', 'group' => 'Completo'],
            ['id' => 'reconocimiento-facial', 'label' => 'Reconocimiento facial', 'input' => 'dni', 'color' => 'bg-[#5D87FF]', 'group' => 'Completo'],
            ['id' => 'justicia', 'label' => 'Justicia', 'input' => 'dni', 'color' => 'bg-red-500', 'group' => 'Completo'],
            ['id' => 'sentinel', 'label' => 'Sentinel', 'input' => 'dni', 'color' => 'bg-orange-500', 'group' => 'Completo'],
            ['id' => 'vehiculo', 'label' => 'Vehículo', 'input' => 'plate', 'color' => 'bg-rose-500', 'group' => 'Completo'],
            ['id' => 'siguele-plus', 'label' => 'Síguelo Plus', 'input' => 'dni', 'color' => 'bg-fuchsia-500', 'group' => 'Completo'],
            ['id' => 'actas', 'label' => 'Actas', 'input' => 'dni', 'color' => 'bg-pink-500', 'group' => 'Completo'],
            ['id' => 'doxing', 'label' => 'Doxing', 'input' => 'dni', 'color' => 'bg-purple-500', 'group' => 'Completo'],
            ['id' => 'persona-plus', 'label' => 'Persona Plus', 'input' => 'dni', 'color' => 'bg-sky-500', 'group' => 'Completo'],
            ['id' => 'sunat', 'label' => 'Sunat', 'input' => 'ruc', 'color' => 'bg-lime-500', 'group' => 'Completo'],
        ];
    }

    public function seleccionarHerramienta(string $id)
    {
        $this->herramienta = $id;
        $herramienta = collect($this->herramientas)->firstWhere('id', $id);

        if ($id !== 'consultadni') {
            if ($herramienta['input'] === 'ruc') {
                $this->tipo = 'ruc';
            } elseif ($herramienta['input'] === 'dni') {
                $this->tipo = 'dni';
            }
        }

        $this->resultado = null;
        $this->showModal = false;
        $this->documento = '';
        $this->searchTerm = '';
    }

    public function consultar()
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $isSimple = $this->modo === 'simple';
        $inputType = $isSimple ? $this->tipo : (collect($this->herramientas)->firstWhere('id', $this->herramienta)['input'] ?? 'dni');
        $inputLabel = match ($inputType) {
            'dni' => 'DNI (8 dígitos)',
            'ruc' => 'RUC (11 dígitos)',
            'plate' => 'Placa (ej: ABC-123)',
            'name' => 'Nombres',
            default => 'Documento',
        };

        $rules = match ($inputType) {
            'dni' => ['documento' => 'digits:8'],
            'ruc' => ['documento' => 'digits:11'],
            'plate' => ['documento' => 'regex:/^[A-Za-z0-9\-]{3,10}$/'],
            'name' => ['searchTerm' => 'required|min:3'],
            default => ['documento' => 'required'],
        };

        $this->validate($rules, [
            'documento.digits' => "El campo debe ser $inputLabel",
            'documento.regex' => 'Formato de placa inválido',
            'searchTerm.required' => 'Ingrese términos de búsqueda',
            'searchTerm.min' => 'Mínimo 3 caracteres',
        ]);

        try {
            $service = app(DniConsultaService::class);
            if ($isSimple && $this->tipo === 'ruc') {
                $data = $service->consultarRuc($this->documento);
            } else {
                $data = $service->consultarHerramienta($this->herramienta, $this->documento, $this->searchTerm);
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'Error de conexión: '.$e->getMessage(), type: 'error');
            return;
        }

        if ($data) {
            $this->resultado = $data;
            $this->showModal = true;

            $herramientaLabel = collect($this->herramientas)->firstWhere('id', $this->herramienta)['label'] ?? $this->herramienta;
            $this->modalTitle = 'RESULTADO: '.strtoupper($herramientaLabel);

            $nombreMostrar = $data['nombre_completo'] ?? $data['razon_social'] ?? $data['nombre'] ?? $this->documento ?: $this->searchTerm;

            ConsultaHistorial::create([
                'user_id' => auth()->id(),
                'tipo' => strtoupper($herramientaLabel),
                'documento' => $this->documento ?: $this->searchTerm,
                'resultado_json' => $data,
                'nombre_mostrar' => $nombreMostrar,
            ]);

            $this->cargarHistorial();
            $this->dispatch('notify', message: 'Consulta exitosa: '.$nombreMostrar, type: 'success');
        } else {
            $this->resultado = ['error' => true];
            $this->showModal = true;
            $herramientaLabel = collect($this->herramientas)->firstWhere('id', $this->herramienta)['label'] ?? $this->herramienta;
            $this->modalTitle = 'RESULTADO: '.strtoupper($herramientaLabel);
            $this->dispatch('notify', message: 'No se encontraron resultados', type: 'warning');
        }
    }

    public function verResultado($index)
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
        $entry = $this->historial[$index] ?? null;
        if ($entry) {
            $full = ConsultaHistorial::find($entry['id']);
            $this->resultado = $full?->resultado_json;
            $this->documento = $entry['documento'];
            $this->searchTerm = $entry['documento'];
            $this->modalTitle = 'RESULTADO: '.strtoupper($entry['tipo']);
            $this->showModal = true;
        }
    }

    public function limpiarHistorial()
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
        ConsultaHistorial::where('user_id', auth()->id())->delete();
        $this->historial = [];
        $this->dispatch('notify', message: 'Historial eliminado.', type: 'success');
    }

    public function render()
    {
        return view('livewire.olimpo.consultas-dni', [
            'resultado' => $this->resultado,
        ])->layout('layouts.olimpo', ['title' => 'Consultas DNI / RUC']);
    }
}
