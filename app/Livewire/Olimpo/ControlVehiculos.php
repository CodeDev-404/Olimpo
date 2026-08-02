<?php

namespace App\Livewire\Olimpo;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Camioneta;
use App\Models\Combustible;
use App\Models\ControlVehiculo;
use Illuminate\Support\Facades\DB;

class ControlVehiculos extends Component
{
    use WithPagination;

    public $camionetas = [];
    public $combustibles = [];

    // Control BMA-828 form fields
    public $selectedId = null;
    public $showForm = false;
    public $editId = null;
    public $fecha = '';
    public $chofer = '';
    public $placa = '';
    public $marca = '';
    public $modelo = '';
    public $clase = '';
    public $hora_salida = '';
    public $km_salida = '';
    public $hora_ingreso = '';
    public $km_ingreso = '';
    public $observacion = '';

    // Filters
    public $search = '';
    public $filterFecha = '';

    // Select mode for BMA-828
    public $selectMode = false;
    public $selectedIds = [];

    // Combustibles CRUD
    public $selectedCombustibleId = null;
    public $showFormCombustible = false;
    public $editIdCombustible = null;
    public $combFecha = '';
    public $combCategoria = '';
    public $combClase = '';
    public $combMarca = '';
    public $combPlaca = '';
    public $combModelo = '';
    public $combAnio = '';
    public $combColor = '';
    public $combConductor = '';
    public $combKilometraje = '';
    public $combCombustible = '';
    public $combGalones = '';
    public $combPrecioGalon = '';
    public $combTotal = '';

    // Combustibles filters
    public $searchCombustible = '';
    public $filterFechaCombustible = '';

    // Select mode for Combustibles
    public $selectModeCombustible = false;
    public $selectedIdsCombustible = [];

    protected $listeners = ['importData' => 'handleImport'];

    protected $paginationTheme = 'tailwind';

    public function mount()
    {
        $this->loadCamionetas();
        $this->fecha = now()->format('d/m/Y');
        $this->combFecha = now()->format('d/m/Y');
    }

    public function loadCamionetas()
    {
        $this->camionetas = Camioneta::orderBy('placa')->get()->toArray();
    }

    public function updatedSearch() { $this->resetPage(); }
    public function updatedFilterFecha() { $this->resetPage(); }
    public function updatedSearchCombustible() { $this->resetPageCombustible(); }
    public function updatedFilterFechaCombustible() { $this->resetPageCombustible(); }

    // ========== BMA-828 CRUD ==========

    public function selectRegistro($id)
    {
        $this->selectedId = $id;
    }

    public function nuevo()
    {
        $this->resetForm();
        $this->fecha = now()->format('d/m/Y');
        $this->showForm = true;
        $this->editId = null;
    }

    public function editar()
    {
        if (!$this->selectedId) {
            $this->dispatch('notify', message: 'Selecciona un registro primero.', type: 'warning');
            return;
        }
        $r = ControlVehiculo::find($this->selectedId);
        if (!$r) return;
        $this->fillForm($r);
        $this->showForm = true;
        $this->editId = $r->id;
    }

    public function duplicar()
    {
        if (!$this->selectedId) {
            $this->dispatch('notify', message: 'Selecciona un registro primero.', type: 'warning');
            return;
        }
        $r = ControlVehiculo::find($this->selectedId);
        if (!$r) return;
        $this->fillForm($r);
        $this->fecha = now()->format('d/m/Y');
        $this->showForm = true;
        $this->editId = null;
    }

    public function eliminar()
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
        if (!$this->selectedId) {
            $this->dispatch('notify', message: 'Selecciona un registro primero.', type: 'warning');
            return;
        }
        $r = ControlVehiculo::find($this->selectedId);
        if ($r) {
            $r->delete();
            $this->selectedId = null;
            $this->resetPage();
            $this->dispatch('notify', message: 'Registro eliminado.', type: 'success');
        }
    }

    public function save()
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $this->validate([
            'fecha' => 'required',
            'chofer' => 'required',
        ]);

        $data = [
            'fecha' => $this->fecha,
            'chofer' => $this->chofer,
            'placa' => $this->placa,
            'marca' => $this->marca,
            'modelo' => $this->modelo,
            'clase' => $this->clase,
            'hora_salida' => $this->hora_salida,
            'km_salida' => $this->km_salida,
            'hora_ingreso' => $this->hora_ingreso,
            'km_ingreso' => $this->km_ingreso,
            'observacion' => $this->observacion,
        ];

        if ($this->editId) {
            $r = ControlVehiculo::find($this->editId);
            if ($r) $r->update($data);
            $this->dispatch('notify', message: 'Registro actualizado.', type: 'success');
        } else {
            ControlVehiculo::create($data);
            $this->dispatch('notify', message: 'Registro guardado.', type: 'success');
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

    public function limpiarFiltros()
    {
        $this->search = '';
        $this->filterFecha = '';
        $this->resetPage();
    }

    private function resetForm()
    {
        $this->fecha = now()->format('d/m/Y');
        $this->chofer = '';
        $this->placa = '';
        $this->marca = '';
        $this->modelo = '';
        $this->clase = '';
        $this->hora_salida = '';
        $this->km_salida = '';
        $this->hora_ingreso = '';
        $this->km_ingreso = '';
        $this->observacion = '';
    }

    private function fillForm($r)
    {
        $this->fecha = $r->fecha;
        $this->chofer = $r->chofer;
        $this->placa = $r->placa;
        $this->marca = $r->marca;
        $this->modelo = $r->modelo;
        $this->clase = $r->clase;
        $this->hora_salida = $r->hora_salida;
        $this->km_salida = $r->km_salida;
        $this->hora_ingreso = $r->hora_ingreso;
        $this->km_ingreso = $r->km_ingreso;
        $this->observacion = $r->observacion;
    }

    // ========== BMA-828 Select Mode ==========

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
        $currentIds = array_map('intval', $this->registros->pluck('id')->toArray());
        $current = array_values(array_unique(array_map('intval', $this->selectedIds)));
        $allSelected = $currentIds && !array_diff($currentIds, $current);
        if ($allSelected) {
            $this->selectedIds = array_values(array_diff($current, $currentIds));
        } else {
            $this->selectedIds = array_values(array_unique(array_merge($current, $currentIds)));
        }
    }

    public function eliminarSeleccionados()
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
        $ids = $this->selectedIds;
        if (empty($ids)) {
            $this->dispatch('notify', message: 'Selecciona uno o más registros.', type: 'warning');
            return;
        }
        $count = ControlVehiculo::whereIn('id', $ids)->delete();
        $this->selectedIds = [];
        $this->selectedId = null;
        $this->resetPage();
        $this->dispatch('notify', message: $count . ' registro(s) eliminado(s).', type: 'success');
    }

    // ========== Combustibles CRUD ==========

    public function selectCombustible($id)
    {
        $this->selectedCombustibleId = $id;
    }

    public function nuevoCombustible()
    {
        $this->resetFormCombustible();
        $this->combFecha = now()->format('d/m/Y');
        $this->showFormCombustible = true;
        $this->editIdCombustible = null;
    }

    public function editarCombustible()
    {
        if (!$this->selectedCombustibleId) {
            $this->dispatch('notify', message: 'Selecciona un registro primero.', type: 'warning');
            return;
        }
        $r = Combustible::find($this->selectedCombustibleId);
        if (!$r) return;
        $this->fillFormCombustible($r);
        $this->showFormCombustible = true;
        $this->editIdCombustible = $r->id;
    }

    public function duplicarCombustible()
    {
        if (!$this->selectedCombustibleId) {
            $this->dispatch('notify', message: 'Selecciona un registro primero.', type: 'warning');
            return;
        }
        $r = Combustible::find($this->selectedCombustibleId);
        if (!$r) return;
        $this->fillFormCombustible($r);
        $this->combFecha = now()->format('d/m/Y');
        $this->showFormCombustible = true;
        $this->editIdCombustible = null;
    }

    public function eliminarCombustible()
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
        if (!$this->selectedCombustibleId) {
            $this->dispatch('notify', message: 'Selecciona un registro primero.', type: 'warning');
            return;
        }
        $r = Combustible::find($this->selectedCombustibleId);
        if ($r) {
            $r->delete();
            $this->selectedCombustibleId = null;
            $this->resetPageCombustible();
            $this->dispatch('notify', message: 'Registro eliminado.', type: 'success');
        }
    }

    public function saveCombustible()
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $this->validate([
            'combFecha' => 'required',
            'combCombustible' => 'required',
            'combGalones' => 'required|numeric|min:0',
        ], [
            'combFecha.required' => 'La fecha es requerida.',
            'combCombustible.required' => 'El tipo de combustible es requerido.',
            'combGalones.required' => 'Los galones son requeridos.',
            'combGalones.numeric' => 'Los galones deben ser un número.',
        ]);

        $data = [
            'fecha' => $this->combFecha,
            'categoria' => $this->combCategoria,
            'clase' => $this->combClase,
            'marca' => $this->combMarca,
            'placa' => $this->combPlaca,
            'modelo' => $this->combModelo,
            'anio' => $this->combAnio,
            'color' => $this->combColor,
            'conductor' => $this->combConductor,
            'kilometraje' => $this->combKilometraje,
            'combustible' => $this->combCombustible,
            'galones' => (float) str_replace(',', '', $this->combGalones),
            'precio_galon' => (float) str_replace(',', '', $this->combPrecioGalon ?: 0),
            'total' => (float) str_replace(',', '', $this->combTotal ?: 0),
        ];

        if ($this->editIdCombustible) {
            $r = Combustible::find($this->editIdCombustible);
            if ($r) $r->update($data);
            $this->dispatch('notify', message: 'Registro actualizado.', type: 'success');
        } else {
            Combustible::create($data);
            $this->dispatch('notify', message: 'Registro guardado.', type: 'success');
        }

        $this->showFormCombustible = false;
        $this->selectedCombustibleId = null;
        $this->editIdCombustible = null;
        $this->resetPageCombustible();
    }

    public function cancelCombustible()
    {
        $this->showFormCombustible = false;
        $this->editIdCombustible = null;
    }

    public function limpiarFiltrosCombustible()
    {
        $this->searchCombustible = '';
        $this->filterFechaCombustible = '';
        $this->resetPageCombustible();
    }

    private function resetFormCombustible()
    {
        $this->combFecha = now()->format('d/m/Y');
        $this->combCategoria = '';
        $this->combClase = '';
        $this->combMarca = '';
        $this->combPlaca = '';
        $this->combModelo = '';
        $this->combAnio = '';
        $this->combColor = '';
        $this->combConductor = '';
        $this->combKilometraje = '';
        $this->combCombustible = '';
        $this->combGalones = '';
        $this->combPrecioGalon = '';
        $this->combTotal = '';
    }

    private function fillFormCombustible($r)
    {
        $this->combFecha = $r->fecha;
        $this->combCategoria = $r->categoria;
        $this->combClase = $r->clase;
        $this->combMarca = $r->marca;
        $this->combPlaca = $r->placa;
        $this->combModelo = $r->modelo;
        $this->combAnio = $r->anio;
        $this->combColor = $r->color;
        $this->combConductor = $r->conductor;
        $this->combKilometraje = $r->kilometraje;
        $this->combCombustible = $r->combustible;
        $this->combGalones = $r->galones;
        $this->combPrecioGalon = $r->precio_galon;
        $this->combTotal = $r->total;
    }

    // ========== Combustibles Select Mode ==========

    public function toggleSelectModeCombustible()
    {
        $this->selectModeCombustible = !$this->selectModeCombustible;
        if (!$this->selectModeCombustible) $this->selectedIdsCombustible = [];
    }

    public function toggleSelectCombustible($id)
    {
        $key = array_search($id, $this->selectedIdsCombustible);
        if ($key !== false) {
            unset($this->selectedIdsCombustible[$key]);
        } else {
            $this->selectedIdsCombustible[] = $id;
        }
        $this->selectedIdsCombustible = array_values($this->selectedIdsCombustible);
    }

    public function toggleSelectAllCombustible()
    {
        $currentIds = array_map('intval', $this->registrosCombustibles->pluck('id')->toArray());
        $current = array_values(array_unique(array_map('intval', $this->selectedIdsCombustible)));
        $allSelected = $currentIds && !array_diff($currentIds, $current);
        if ($allSelected) {
            $this->selectedIdsCombustible = array_values(array_diff($current, $currentIds));
        } else {
            $this->selectedIdsCombustible = array_values(array_unique(array_merge($current, $currentIds)));
        }
    }

    public function eliminarSeleccionadosCombustible()
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
        $ids = $this->selectedIdsCombustible;
        if (empty($ids)) {
            $this->dispatch('notify', message: 'Selecciona uno o más registros.', type: 'warning');
            return;
        }
        $count = Combustible::whereIn('id', $ids)->delete();
        $this->selectedIdsCombustible = [];
        $this->selectedCombustibleId = null;
        $this->resetPageCombustible();
        $this->dispatch('notify', message: $count . ' registro(s) eliminado(s).', type: 'success');
    }

    // ========== Import ==========

    public function handleImport($rows, $panel = 'control_vehiculos')
    {
        if ($panel === 'control_vehiculos') {
            $result = \App\Imports\ControlVehiculosImport::insert($rows ?? []);
            $msg = $result['inserted'] . ' registro(s) de control vehicular importados correctamente.';
            if (!empty($result['errors'])) {
                $msg .= ' Errores: ' . implode(' | ', $result['errors']);
            }
            $this->dispatch('notify', message: $msg, type: empty($result['errors']) ? 'success' : 'warning');
            $this->resetPage();
        } elseif ($panel === 'combustibles') {
            $result = \App\Imports\CombustiblesImport::insert($rows ?? []);
            $msg = $result['inserted'] . ' registro(s) de combustible importados correctamente.';
            if (!empty($result['errors'])) {
                $msg .= ' Errores: ' . implode(' | ', $result['errors']);
            }
            $this->dispatch('notify', message: $msg, type: empty($result['errors']) ? 'success' : 'warning');
            $this->resetPageCombustible();
        }
    }

    // ========== Autocomplete helpers (batched + cached) ==========

    private ?array $_bmaAuto = null;

    private function getBmaAuto(): array
    {
        if ($this->_bmaAuto !== null) return $this->_bmaAuto;
        return $this->_bmaAuto = cache()->remember('cv_bma_auto', 300, function () {
            $sql = "SELECT 'choferes' AS src, chofer AS val FROM control_vehiculos WHERE chofer IS NOT NULL AND chofer != '' AND chofer != '0'
                    UNION SELECT 'placas', placa FROM control_vehiculos WHERE placa IS NOT NULL AND placa != ''
                    UNION SELECT 'marcas', marca FROM control_vehiculos WHERE marca IS NOT NULL AND marca != ''
                    UNION SELECT 'modelos', modelo FROM control_vehiculos WHERE modelo IS NOT NULL AND modelo != ''
                    UNION SELECT 'clases', clase FROM control_vehiculos WHERE clase IS NOT NULL AND clase != ''";
            $rows = DB::select($sql);
            $data = ['choferes' => [], 'placas' => [], 'marcas' => [], 'modelos' => [], 'clases' => []];
            foreach ($rows as $r) {
                $data[$r->src][] = $r->val;
            }
            foreach ($data as &$list) {
                $list = array_values(array_unique($list));
                sort($list);
            }
            return $data;
        });
    }

    private ?array $_combAuto = null;

    private function getCombAuto(): array
    {
        if ($this->_combAuto !== null) return $this->_combAuto;
        return $this->_combAuto = cache()->remember('cv_comb_auto', 300, function () {
            $sql = "SELECT 'categorias' AS src, categoria AS val FROM combustibles WHERE categoria IS NOT NULL AND categoria != ''
                    UNION SELECT 'clases', clase FROM combustibles WHERE clase IS NOT NULL AND clase != ''
                    UNION SELECT 'marcas', marca FROM combustibles WHERE marca IS NOT NULL AND marca != ''
                    UNION SELECT 'placas', placa FROM combustibles WHERE placa IS NOT NULL AND placa != ''
                    UNION SELECT 'modelos', modelo FROM combustibles WHERE modelo IS NOT NULL AND modelo != ''
                    UNION SELECT 'conductores', conductor FROM combustibles WHERE conductor IS NOT NULL AND conductor != ''
                    UNION SELECT 'combustibles', combustible FROM combustibles WHERE combustible IS NOT NULL AND combustible != ''";
            $rows = DB::select($sql);
            $data = ['categorias' => [], 'clases' => [], 'marcas' => [], 'placas' => [], 'modelos' => [], 'conductores' => [], 'combustibles' => []];
            foreach ($rows as $r) {
                $data[$r->src][] = $r->val;
            }
            foreach ($data as &$list) {
                $list = array_values(array_unique($list));
                sort($list);
            }
            return $data;
        });
    }

    // ========== Autocomplete properties ==========

    public function getChoferesProperty() { return $this->getBmaAuto()['choferes']; }
    public function getPlacasProperty() { return $this->getBmaAuto()['placas']; }
    public function getMarcasProperty() { return $this->getBmaAuto()['marcas']; }
    public function getModelosProperty() { return $this->getBmaAuto()['modelos']; }
    public function getClasesProperty() { return $this->getBmaAuto()['clases']; }

    public function getCombCategoriasProperty() { return $this->getCombAuto()['categorias']; }
    public function getCombClasesProperty() { return $this->getCombAuto()['clases']; }
    public function getCombMarcasProperty() { return $this->getCombAuto()['marcas']; }
    public function getCombPlacasProperty() { return $this->getCombAuto()['placas']; }
    public function getCombModelosProperty() { return $this->getCombAuto()['modelos']; }
    public function getCombConductoresProperty() { return $this->getCombAuto()['conductores']; }
    public function getCombustiblesListProperty() { return $this->getCombAuto()['combustibles']; }

    // ========== Render ==========

    public function render()
    {
        // BMA-828 query
        $query = ControlVehiculo::query();

        if ($this->search) {
            $query->where(accent_insensitive_search([
                'chofer', 'placa', 'marca', 'modelo', 'clase', 'observacion',
            ], $this->search));
        }

        if ($this->filterFecha) {
            $f = \Carbon\Carbon::parse($this->filterFecha);
            $query->where('fecha', $f->format('d/m/Y'));
        }

        $totalRegistros = (clone $query)->count();
        $enRuta = (clone $query)->where(function ($q) {
            $q->whereNull('hora_ingreso')->orWhere('hora_ingreso', '');
        })->count();
        $retornados = $totalRegistros - $enRuta;

        $registros = $query->orderBy('fecha', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(25, pageName: 'page');

        // Combustibles query
        $queryComb = Combustible::query();

        if ($this->searchCombustible) {
            $queryComb->where(accent_insensitive_search([
                'combustible', 'conductor', 'placa', 'marca', 'modelo', 'clase', 'categoria',
            ], $this->searchCombustible));
        }

        if ($this->filterFechaCombustible) {
            $f = \Carbon\Carbon::parse($this->filterFechaCombustible);
            $queryComb->where('fecha', $f->format('d/m/Y'));
        }

        $totalGalones = (clone $queryComb)->sum('galones');
        $totalMonto = (clone $queryComb)->sum('total');

        $registrosCombustibles = $queryComb->orderBy('fecha', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(25, pageName: 'combustiblePage');

        return view('livewire.olimpo.control-vehiculos', [
            'registros' => $registros,
            'registrosCombustibles' => $registrosCombustibles,
            'totalRegistros' => $totalRegistros,
            'enRuta' => $enRuta,
            'retornados' => $retornados,
            'totalGalones' => $totalGalones,
            'totalMonto' => $totalMonto,
            'choferes' => $this->choferes,
            'placas' => $this->placas,
            'marcas' => $this->marcas,
            'modelos' => $this->modelos,
            'clases' => $this->clases,
            'combCategorias' => $this->combCategorias,
            'combClases' => $this->combClases,
            'combMarcas' => $this->combMarcas,
            'combPlacas' => $this->combPlacas,
            'combModelos' => $this->combModelos,
            'combConductores' => $this->combConductores,
            'combustiblesList' => $this->combustiblesList,
        ])->layout('layouts.olimpo', ['title' => 'Control Vehículos']);
    }

    private function resetPageCombustible()
    {
        $this->setPage(1, 'combustiblePage');
    }
}
