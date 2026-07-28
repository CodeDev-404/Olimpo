<?php

namespace App\Livewire\Olimpo;

use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class ImportModal extends Component
{
    use WithFileUploads;

    public $panel;
    public $show = false;
    public $file;
    public $rows = [];
    public $selectedRows = [];
    public $importing = false;

    protected $listeners = ['openImportModal' => 'open', 'openImportModalFor' => 'openForPanel'];

    protected $rules = [
        'file' => 'required|mimes:xlsx,xls,csv|max:5120',
    ];

    public function mount($panel = 'ocurrencias')
    {
        $this->panel = $panel;
    }

    public function open()
    {
        $this->reset(['file', 'rows', 'selectedRows', 'importing']);
        $this->show = true;
    }

    public function close()
    {
        $this->show = false;
    }

    public function updatedFile()
    {
        $this->validate();
        $this->importing = true;

        try {
            $raw = Excel::toArray([], $this->file->getRealPath());
            $sheet = $raw[0] ?? [];
            $this->rows = $this->parseRows($sheet);
            $this->selectedRows = collect($this->rows)->where('valid', true)->keys()->toArray();

            $totalCount = count($this->rows);
            $validCount = collect($this->rows)->where('valid', true)->count();
            if ($totalCount > 0 && $validCount === 0) {
                $first = $this->rows[0];
                $errores = implode('; ', array_slice($first['errors'] ?? [], 0, 3));
                $columnas = implode(', ', array_keys($first['data'] ?? []));
                $this->dispatch('notify', message: 'Ninguna fila válida. Errores ej: ' . $errores . ' | Columnas: ' . $columnas, type: 'warning');
            }
        } catch (\Exception $e) {
            \Log::error('ImportModal error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $this->dispatch('notify', message: 'Error al leer el archivo: ' . $e->getMessage(), type: 'danger');
            $this->rows = [];
        }

        $this->importing = false;
    }

    public function selectAll()
    {
        $this->selectedRows = collect($this->rows)->where('valid', true)->keys()->toArray();
    }

    public function deselectAll()
    {
        $this->selectedRows = [];
    }

    public function openForPanel($panel)
    {
        $this->panel = $panel;
        $this->open();
    }

    public function confirm()
    {
        $toImport = collect($this->rows)->filter(fn($r, $i) => in_array($i, $this->selectedRows))->values()->toArray();

        if (empty($toImport)) {
            $this->dispatch('notify', message: 'No hay filas seleccionadas para importar.', type: 'warning');
            return;
        }

        $this->dispatch('importData', rows: $toImport, panel: $this->panel);
        $this->dispatch('notify', message: count($toImport) . ' registro(s) enviados para importar.', type: 'success');
        $this->close();
    }

    private function parseRows(array $sheet): array
    {
        if (empty($sheet)) return [];

        // Find first non-empty row to use as header
        $headerRow = null;
        foreach ($sheet as $idx => $row) {
            $vals = array_map('trim', array_map('strval', $row ?? []));
            if (implode('', $vals) !== '') {
                $headerRow = $idx;
                break;
            }
        }
        if ($headerRow === null) return [];

        $header = array_map('trim', array_map('strval', $sheet[$headerRow]));
        $header = array_map('strtolower', $header);
        $header = array_map(fn($h) => Str::ascii($h), $header);
        $header = $this->normalizeHeaders($header);
        // Remove trailing empty headers
        while ($header && end($header) === '') array_pop($header);

        $rows = [];
        for ($i = $headerRow + 1; $i < count($sheet); $i++) {
            $vals = array_map(fn($v) => trim((string)($v ?? '')), $sheet[$i]);
            // Skip empty rows
            if (implode('', $vals) === '') continue;
            // Handle column count mismatch — truncate or pad
            if (count($vals) > count($header)) $vals = array_slice($vals, 0, count($header));
            while (count($vals) < count($header)) $vals[] = '';
            $row = array_combine($header, $vals);
            if (!$row) continue;
            $row['_index'] = $i;
            $result = $this->validateRow($row);
            $rows[] = $result;
        }

        return $rows;
    }

    private function normalizeHeaders(array $headers): array
    {
        $map = [
            'fecha' => 'fecha', 'date' => 'fecha', 'dia' => 'fecha', 'fecha de cumpleanos' => 'fecha',
            'fecha nacimiento' => 'fecha', 'fecha de nacimiento' => 'fecha', 'cumpleanos' => 'fecha',
            'nombre' => 'nombre', 'name' => 'nombre', 'nombres' => 'nombre', 'nombre completo' => 'nombre',
            'apellidos y nombres' => 'nombre', 'nombres y apellidos' => 'nombre', 'personal' => 'nombre',
            'parentesco' => 'parentesco', 'parentezco' => 'parentesco', 'relacion' => 'parentesco',
            'detalles' => 'detalles', 'detalle' => 'detalles', 'descripcion' => 'detalles',
            'description' => 'detalles', 'observacion' => 'detalles', 'obs' => 'detalles',
            'recordatorio' => 'recordatorio_activo', 'recordatorio activo' => 'recordatorio_activo', 'alarma' => 'recordatorio_activo',
            'hora recordatorio' => 'recordatorio_hora', 'hora de recordatorio' => 'recordatorio_hora', 'hora alarma' => 'recordatorio_hora',
            'hora ingreso' => 'hora_ingreso', 'hora entrada' => 'hora_ingreso', 'h. ingreso' => 'hora_ingreso',
            'hora_ingreso' => 'hora_ingreso', 'hora de ingreso' => 'hora_ingreso', 'ingreso' => 'hora_ingreso',
            'hora salida' => 'hora_salida', 'hora_salida' => 'hora_salida', 'h. salida' => 'hora_salida',
            'hora de salida' => 'hora_salida', 'salida' => 'hora_salida',
            'tipo' => 'tipo', 'type' => 'tipo', 'tipologia' => 'tipo', 'clase' => 'tipo',
            'otro' => 'otro', 'cargo' => 'cargo',
            'departamento' => 'departamento', 'depto' => 'departamento',
            'dni' => 'documento', 'documento' => 'documento', 'doc' => 'documento', 'nro documento' => 'documento',
            'telefono' => 'telefono', 'tel' => 'telefono', 'telf' => 'telefono',
            'celular' => 'telefono', 'movil' => 'telefono',
            'email' => 'email', 'correo' => 'email', 'e-mail' => 'email', 'mail' => 'email',
            'estado' => 'estado', 'situacion' => 'estado',
            'observacion' => 'observacion', 'observaciones' => 'observacion', 'obs.' => 'observacion',
            'turno' => 'turno',
            'tardanza' => 'tardanza_min', 'tardanza min' => 'tardanza_min', 'tardanza (min)' => 'tardanza_min',
            'horas' => 'horas_trabajadas', 'horas trabajadas' => 'horas_trabajadas', 'h. trabajadas' => 'horas_trabajadas',
            'etiqueta' => 'etiqueta', 'calificacion' => 'etiqueta',

            // Control Vehículos
            'chofer' => 'chofer', 'conductor' => 'chofer', 'driver' => 'chofer',
            'placa' => 'placa', 'patente' => 'placa', 'license plate' => 'placa',
            'marca' => 'marca', 'brand' => 'marca',
            'modelo' => 'modelo', 'model' => 'modelo',
            'clase' => 'clase', 'tipo vehiculo' => 'clase', 'tipo' => 'clase',
            'hora salida' => 'hora_salida', 'hora_salida' => 'hora_salida', 'h. salida' => 'hora_salida',
            'hora ingreso' => 'hora_ingreso', 'hora_ingreso' => 'hora_ingreso', 'h. ingreso' => 'hora_ingreso',
            'km salida' => 'km_salida', 'km_salida' => 'km_salida', 'kms salida' => 'km_salida',
            'km ingreso' => 'km_ingreso', 'km_ingreso' => 'km_ingreso', 'kms ingreso' => 'km_ingreso',
            'observacion' => 'observacion', 'observaciones' => 'observacion', 'obs' => 'observacion',

            // Combustibles
            'categoria' => 'categoria', 'cat' => 'categoria', 'category' => 'categoria',
            'anio' => 'anio', 'año' => 'anio', 'year' => 'anio',
            'color' => 'color', 'colour' => 'color',
            'kilometraje' => 'kilometraje', 'km' => 'kilometraje', 'kms' => 'kilometraje', 'odometro' => 'kilometraje',
            'combustible' => 'combustible', 'tipo combustible' => 'combustible', 'fuel' => 'combustible',
            'galones' => 'galones', 'galon' => 'galones', 'gallons' => 'galones',
            'precio galon' => 'precio_galon', 'precio_galon' => 'precio_galon', 'precio x galon' => 'precio_galon',
            'precio' => 'precio_galon', 'price' => 'precio_galon',
            'total' => 'total', 'importe' => 'total', 'amount' => 'total',
        ];
        return array_map(fn($h) => $map[$h] ?? $h, $headers);
    }

    private function validateRow(array $row): array
    {
        return match ($this->panel) {
            'ocurrencias' => $this->validateOcurrencia($row),
            'personal' => $this->validatePersonal($row),
            'asistencia' => $this->validateAsistencia($row),
            'cumpleanos' => $this->validateCumpleano($row),
            'control_vehiculos' => $this->validateControlVehiculo($row),
            'combustibles' => $this->validateCombustible($row),
            default => ['valid' => false, 'errors' => ['Panel desconocido'], 'data' => $row],
        };
    }

    private function validateOcurrencia(array $row): array
    {
        $errors = [];
        $data = [];

        if (empty($row['fecha'])) $errors[] = 'Fecha requerida';
        else {
            $data['fecha'] = $row['fecha'];
            $parts = explode('/', $row['fecha']);
            $data['mes'] = (int)($parts[1] ?? now()->month);
            $data['anio'] = (int)($parts[2] ?? now()->year);
        }

        $data['hora_ingreso'] = $row['hora_ingreso'] ?? $row['h. ingreso'] ?? $row['hora ingreso'] ?? '';
        $data['hora_salida'] = $row['hora_salida'] ?? $row['h. salida'] ?? $row['hora salida'] ?? '';

        if (empty($row['nombre'])) $errors[] = 'Nombre requerido';
        else $data['persona_nombre'] = $row['nombre'];

        $data['tipo'] = $row['tipo'] ?? '';
        $data['otro'] = $row['otro'] ?? $row['cargo'] ?? '';
        $data['detalles'] = $row['detalles'] ?? $row['detalle'] ?? '';
        $data['observacion'] = $row['observacion'] ?? $row['obs'] ?? $row['observación'] ?? $row['obs.'] ?? '';

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'data' => $data,
            'preview' => [
                'fecha' => $data['fecha'] ?? '—',
                'nombre' => $data['persona_nombre'] ?? '—',
                'tipo' => $data['tipo'] ?? '—',
                'detalles' => Str::limit($data['detalles'] ?? '', 40),
            ],
        ];
    }

    private function validatePersonal(array $row): array
    {
        $errors = [];
        $data = [];

        if (empty($row['nombre'])) $errors[] = 'Nombre requerido';
        else $data['nombre'] = $row['nombre'];

        $data['cargo'] = $row['cargo'] ?? '';
        $data['departamento'] = $row['departamento'] ?? $row['depto'] ?? $row['departamento'] ?? '';
        $data['documento'] = $row['documento'] ?? $row['dni'] ?? $row['doc'] ?? '';
        $data['telefono'] = $row['telefono'] ?? $row['teléfono'] ?? $row['tel'] ?? $row['telf'] ?? '';
        $data['email'] = $row['email'] ?? $row['correo'] ?? $row['e-mail'] ?? '';
        $data['estado'] = strtoupper($row['estado'] ?? '') === 'INACTIVO' ? 'INACTIVO' : 'ACTIVO';
        $data['hora_entrada'] = $row['hora_entrada'] ?? $row['h. entrada'] ?? '';
        $data['hora_salida'] = $row['hora_salida'] ?? $row['h. salida'] ?? '';

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'data' => $data,
            'preview' => [
                'nombre' => $data['nombre'] ?? '—',
                'cargo' => $data['cargo'] ?: '—',
                'documento' => $data['documento'] ?: '—',
                'estado' => $data['estado'],
            ],
        ];
    }

    private function validateAsistencia(array $row): array
    {
        $errors = [];
        $data = [];

        if (empty($row['nombre'])) $errors[] = 'Nombre requerido';
        else $data['persona_nombre'] = $row['nombre'];

        if (empty($row['fecha'])) $errors[] = 'Fecha requerida';
        else $data['fecha'] = $row['fecha'];

        $data['hora_entrada'] = $row['hora_entrada'] ?? $row['h. entrada'] ?? $row['hora ingreso'] ?? $row['hora_ingreso'] ?? '';
        $data['hora_salida'] = $row['hora_salida'] ?? $row['h. salida'] ?? $row['hora salida'] ?? $row['hora_salida'] ?? '';
        $data['turno'] = strtoupper($row['turno'] ?? '') ?: 'DÍA';
        $data['tardanza_min'] = (int)($row['tardanza_min'] ?? $row['tardanza'] ?? $row['tardanza (min)'] ?? 0);
        $data['horas_trabajadas'] = (float)($row['horas_trabajadas'] ?? $row['h. trabajadas'] ?? $row['horas'] ?? 0);
        $data['etiqueta'] = strtoupper($row['etiqueta'] ?? '') ?: 'BUENO';

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'data' => $data,
            'preview' => [
                'nombre' => $data['persona_nombre'] ?? '—',
                'fecha' => $data['fecha'] ?? '—',
                'hora_entrada' => $data['hora_entrada'] ?: '—',
                'turno' => $data['turno'],
                'etiqueta' => $data['etiqueta'],
            ],
        ];
    }

    private function validateCumpleano(array $row): array
    {
        $errors = [];
        $data = [];

        if (empty($row['fecha'])) $errors[] = 'Fecha requerida';
        else $data['fecha'] = $row['fecha'];

        if (empty($row['nombre'])) $errors[] = 'Nombre requerido';
        else $data['nombre'] = $row['nombre'];

        $data['detalles'] = $row['detalles'] ?? $row['detalle'] ?? '';
        $data['parentesco'] = $row['parentesco'] ?? '';
        $data['recordatorio_activo'] = isset($row['recordatorio_activo']) ? filter_var($row['recordatorio_activo'], FILTER_VALIDATE_BOOLEAN) : false;
        $data['recordatorio_hora'] = $row['recordatorio_hora'] ?? '07:30';

        // Validate time format if reminder is active
        if ($data['recordatorio_activo'] && !preg_match('/^\d{2}:\d{2}$/', $data['recordatorio_hora'])) {
            $errors[] = 'Formato de hora inválido (use HH:MM)';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'data' => $data,
            'preview' => [
                'fecha' => $data['fecha'] ?? '—',
                'nombre' => $data['nombre'] ?? '—',
                'detalles' => Str::limit($data['detalles'] ?? '', 40),
            ],
        ];
    }

    private function validateControlVehiculo(array $row): array
    {
        $errors = [];
        $data = [];

        if (empty($row['fecha'])) $errors[] = 'Fecha requerida';
        else $data['fecha'] = $row['fecha'];

        if (empty($row['chofer'])) $errors[] = 'Chofer requerido';
        else $data['chofer'] = $row['chofer'];

        $data['placa'] = $row['placa'] ?? '';
        $data['marca'] = $row['marca'] ?? '';
        $data['modelo'] = $row['modelo'] ?? '';
        $data['clase'] = $row['clase'] ?? $row['tipo'] ?? '';
        $data['hora_salida'] = $row['hora_salida'] ?? '';
        $data['hora_ingreso'] = $row['hora_ingreso'] ?? '';
        $data['km_salida'] = $row['km_salida'] ?? '';
        $data['km_ingreso'] = $row['km_ingreso'] ?? '';
        $data['observacion'] = $row['observacion'] ?? '';

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'data' => $data,
            'preview' => [
                'fecha' => $data['fecha'] ?? '—',
                'chofer' => $data['chofer'] ?? '—',
                'placa' => $data['placa'] ?: '—',
                'marca' => $data['marca'] ?: '—',
            ],
        ];
    }

    private function validateCombustible(array $row): array
    {
        $errors = [];
        $data = [];

        if (empty($row['fecha'])) $errors[] = 'Fecha requerida';
        else $data['fecha'] = $row['fecha'];

        if (empty($row['combustible'])) $errors[] = 'Tipo de combustible requerido';
        else $data['combustible'] = $row['combustible'];

        if (empty($row['galones'])) $errors[] = 'Galones requerido';
        else $data['galones'] = $row['galones'];

        $data['categoria'] = $row['categoria'] ?? '';
        $data['clase'] = $row['clase'] ?? '';
        $data['marca'] = $row['marca'] ?? '';
        $data['placa'] = $row['placa'] ?? '';
        $data['modelo'] = $row['modelo'] ?? '';
        $data['anio'] = $row['anio'] ?? '';
        $data['color'] = $row['color'] ?? '';
        $data['conductor'] = $row['conductor'] ?? $row['chofer'] ?? '';
        $data['kilometraje'] = $row['kilometraje'] ?? $row['km'] ?? '';
        $data['precio_galon'] = $row['precio_galon'] ?? 0;
        $data['total'] = $row['total'] ?? 0;

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'data' => $data,
            'preview' => [
                'fecha' => $data['fecha'] ?? '—',
                'combustible' => $data['combustible'] ?? '—',
                'placa' => $data['placa'] ?: '—',
                'galones' => $data['galones'] ?: '—',
            ],
        ];
    }

    public function render()
    {
        return view('livewire.olimpo.import-modal');
    }
}
