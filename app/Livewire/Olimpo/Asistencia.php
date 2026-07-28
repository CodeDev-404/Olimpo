<?php

namespace App\Livewire\Olimpo;

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\Personal;
use App\Models\Asistencia as AsistenciaModel;
use Illuminate\Support\Facades\DB;

class Asistencia extends Component
{
    public $mes;
    public $anio;
    public $dias = 31;
    public $filterMes;
    public $nameColumnWidth = 150;
    public $editing = null;
    public $editValue = '';
    public $editValueSalida = '';
    public $editTipo = 'ASISTIÓ';
    public $editTurno = 'DÍA';
    public $editDiasCubre = 2;
    public $editFechaFin = '';
    public $showHoras = false;
    public $editLockSalida = false;
    public $editLockEntrada = false;
    public $configHoraEntradaDia = '08:00';
    public $configHoraSalidaDia = '17:00';
    public $configHoraEntradaNoche = '19:00';
    public $configHoraSalidaNoche = '07:00';
    public $configLimBueno = 5;
    public $configLimRegular = 20;

    protected $listeners = ['importData' => 'handleImport'];

    public function updatedFilterMes($value)
    {
        if (preg_match('/^(\d{4})-(\d{2})$/', $value, $m)) {
            $this->anio = (int)$m[1];
            $this->mes = $m[2];
            $this->loadGrid();
        }
    }

    public function mount()
    {
        $this->mes = now()->format('m');
        $this->anio = now()->year;
        $this->filterMes = $this->anio . '-' . $this->mes;
        $this->loadGrid();
    }

    public function loadGrid()
    {
        $this->dias = match ((int)$this->mes) {
            4, 6, 9, 11 => 30,
            2 => ((int)$this->anio % 4 == 0) ? 29 : 28,
            default => 31,
        };

        $this->nameColumnWidth = max(120, collect($this->personal)->reduce(fn($c, $p) => max($c, mb_strlen($p['nombre']) * 8 + 32), 120));

        $config = DB::table('configuracion')->get()->keyBy('clave');
        $this->configHoraEntradaDia = $config['hora_entrada_dia']->valor ?? '08:00';
        $this->configHoraSalidaDia = $config['hora_salida_dia']->valor ?? '17:00';
        $this->configHoraEntradaNoche = $config['hora_entrada_noche']->valor ?? '19:00';
        $this->configHoraSalidaNoche = $config['hora_salida_noche']->valor ?? '07:00';
        $this->configLimBueno = (int)($config['limite_bueno_min']->valor ?? 5);
        $this->configLimRegular = (int)($config['limite_regular_min']->valor ?? 20);
    }

    #[Computed]
    public function getPersonalProperty()
    {
        $deptos = [
            'PRINCIPAL' => 1, 'COMISIONES' => 2,
            'COORDINADOR' => 3, 'SEGURIDAD' => 4,
            'RESIDENCIA' => 5,
            'TORREON' => 6, 'TORREON SUPLENTE' => 6,
        ];

        $grupoPri = ['CHOFERES' => 1, 'OLIMPO' => 2, 'COCINA' => 3, 'MANTENIMIENTO' => 4, 'TORREÓN' => 5];

        $personal = Personal::activos()
            ->with('cargoRel:id,nombre,grupo,orden')
            ->select(['id', 'nombre', 'alias', 'departamento'])
            ->get()
            ->toArray();

        foreach ($personal as &$p) {
            $p['nombre'] = t($p['nombre']);
            $dep = $p['departamento'] ?? '';
            $p['grupo_rol'] = in_array($dep, ['TORREON', 'TORREON SUPLENTE'])
                ? 'TORREÓN'
                : ($p['cargo_rel']['grupo'] ?? 'OLIMPO');
            $p['orden_rol'] = $p['cargo_rel']['orden'] ?? 0;
            unset($p['cargo_rel'], $p['created_at'], $p['updated_at']);
        }
        unset($p);

        usort($personal, function ($a, $b) use ($deptos, $grupoPri) {
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

        return array_values($personal);
    }

    #[Computed]
    public function getGridDataProperty()
    {
        $raw = \DB::select(
            "SELECT id, persona_id, persona_nombre, fecha, hora_entrada, hora_salida, turno, tardanza_min, horas_trabajadas, etiqueta FROM asistencia WHERE SUBSTR(fecha, 4, 2) = ? AND SUBSTR(fecha, 7, 4) = ? ORDER BY persona_nombre, fecha",
            [$this->mes, (string)$this->anio]
        );
        $rows = json_decode(json_encode($raw), true);

        $gridData = [];
        foreach ($rows as $r) {
            $key = $r['persona_id'] . '_' . $r['fecha'];
            $gridData[$key] = $r;
        }
        return $gridData;
    }

    public function editCell($personaId, $dia, $fecha)
    {
        $key = $personaId . '_' . $fecha;
        $reg = $this->gridData[$key] ?? null;
        $this->editing = $key;
        $this->editLockSalida = false;
        $this->editLockEntrada = false;

        if ($reg) {
            $this->editTipo = match ($reg['turno'] ?? '') {
                'FALTA' => 'FALTA',
                'DESCANSO' => 'DESCANSO',
                default => 'ASISTIÓ',
            };
            $this->editValue = $reg['hora_entrada'] ?? '';

            $etiqueta = $reg['etiqueta'] ?? '';
            $turnoReg = $reg['turno'] ?? '';

            if ($etiqueta === '24H' && $turnoReg === '24H') {
                $this->editLockSalida = true;
                $this->editValueSalida = $this->detectarSubTurno($this->editValue) === 'NOCHE'
                    ? $this->configHoraSalidaNoche
                    : $this->configHoraSalidaDia;
            } elseif ($etiqueta === '24H' && $turnoReg !== '24H') {
                $this->editLockEntrada = true;
                $this->editValue = $this->configHoraEntradaDia;
                $this->editValueSalida = $reg['hora_salida'] ?? '';
            } else {
                $this->editValueSalida = $reg['hora_salida'] ?? '';
            }

            $this->editTurno = $turnoReg ?: $this->detectarTurno($this->editValue, $this->editValueSalida);
        } else {
            $this->editTipo = 'ASISTIÓ';
            $this->editTurno = 'DÍA';
            $this->editValue = $this->configHoraEntradaDia;
            $this->editValueSalida = $this->configHoraSalidaDia;
        }
        $this->editDiasCubre = 2;
        $this->actualizarFechaFin();
        $this->showHoras = false;
    }

    public function updatedEditValue()
    {
        $this->editTipo = $this->editValue ? 'ASISTIÓ' : $this->editTipo;
        if ($this->editValue) {
            $this->editTurno = $this->detectarTurno($this->editValue, $this->editValueSalida);
        }
    }

    public function updatedEditValueSalida()
    {
        if ($this->editValueSalida) $this->editTipo = 'ASISTIÓ';
        if ($this->editValue) {
            $this->editTurno = $this->detectarTurno($this->editValue, $this->editValueSalida);
        }
    }

    public function saveCell()
    {
        try {
            if (!$this->editing) {
                $this->dispatch('notify', message: 'No hay celda seleccionada.', type: 'warning');
                return;
            }
            $parts = explode('_', $this->editing);
            $personaId = (int)$parts[0];
            $fecha = substr($this->editing, strlen($parts[0]) + 1);

            $persona = collect($this->personal)->firstWhere('id', $personaId);
            if (!$persona) {
                $this->dispatch('notify', message: 'Persona no encontrada en la lista.', type: 'warning');
                return;
            }

            if ($this->editTipo === 'FALTA') {
                $this->guardarRegistro($personaId, $persona['nombre'], $fecha, '', '', 'FALTA', 0, 0, 'FALTA');
            } elseif ($this->editTipo === 'DESCANSO') {
                $this->guardarRegistro($personaId, $persona['nombre'], $fecha, '', '', 'DESCANSO', 0, 0, 'DESCANSO');
            } else {
                $turno = $this->editTurno;
                [$horas, $tardanza, $etiqueta] = $this->calcular($this->editValue, $this->editValueSalida, $turno);

                if ($turno === '24H' && $this->editValueSalida) {
                    $subTurno = $this->detectarSubTurno($this->editValue);
                    if ($subTurno === 'NOCHE') {
                        $this->guardarRegistro($personaId, $persona['nombre'], $fecha, $this->editValue, $this->configHoraSalidaNoche, $turno, $tardanza, $horas, $etiqueta);
                        $this->crearDiasExtra($personaId, $persona['nombre'], $fecha, $turno);
                    } else {
                        $this->guardarRegistro($personaId, $persona['nombre'], $fecha, $this->editValue, $this->editValueSalida, $turno, $tardanza, $horas, $etiqueta);
                    }
                } else {
                    $this->guardarRegistro($personaId, $persona['nombre'], $fecha, $this->editValue, $this->editValueSalida, $turno, $tardanza, $horas, $etiqueta);
                }
            }

            $this->editing = null;
            $this->editValue = '';
            $this->editValueSalida = '';
            $this->editDiasCubre = 2;
            $this->editFechaFin = '';
            $this->showHoras = false;
            $this->editLockSalida = false;
            $this->editLockEntrada = false;
            $this->loadGrid();
            $this->dispatch('notify', message: 'Asistencia registrada.', type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'Error: ' . $e->getMessage(), type: 'error');
        }
    }

    private function guardarRegistro($personaId, $nombre, $fecha, $entrada, $salida, $turno, $tardanza, $horas, $etiqueta)
    {
        AsistenciaModel::updateOrCreate(
            ['persona_id' => $personaId, 'fecha' => $fecha],
            [
                'persona_nombre' => $nombre,
                'hora_entrada' => $entrada,
                'hora_salida' => $salida,
                'turno' => $turno,
                'tardanza_min' => $tardanza,
                'horas_trabajadas' => $horas,
                'etiqueta' => $etiqueta,
            ]
        );
    }

    private function crearDiasExtra($personaId, $nombre, $fechaBase, $turno)
    {
        $subTurno = $this->detectarSubTurno($this->editValue);
        if ($subTurno !== 'NOCHE') return;

        $fechaObj = \DateTime::createFromFormat('d/m/Y', $fechaBase);
        if (!$fechaObj) return;

        $fechaObj->modify('+1 day');
        $next = $fechaObj->format('d/m/Y');

        [$horas, $tardanza, $etiqueta] = $this->calcular($this->configHoraEntradaDia, $this->editValueSalida, 'DÍA');

        AsistenciaModel::updateOrCreate(
            ['persona_id' => $personaId, 'fecha' => $next],
            [
                'persona_nombre' => $nombre,
                'hora_entrada' => $this->configHoraEntradaDia,
                'hora_salida' => $this->editValueSalida,
                'turno' => 'DÍA',
                'tardanza_min' => $tardanza,
                'horas_trabajadas' => $horas,
                'etiqueta' => $turno,
            ]
        );
    }

    public function actualizarFechaFin()
    {
        if (!$this->editing || $this->editTurno !== '24H') {
            $this->editFechaFin = '';
            return;
        }
        $parts = explode('_', $this->editing);
        $fechaBase = substr($this->editing, strlen($parts[0]) + 1);
        $fechaObj = \DateTime::createFromFormat('d/m/Y', $fechaBase);
        if (!$fechaObj) { $this->editFechaFin = ''; return; }

        $fechaObj->modify('+1 day');
        $this->editFechaFin = $fechaObj->format('d/m/Y');
    }

    public function updatedEditTurno()
    {
        $this->actualizarFechaFin();
        if (!$this->editing || !isset($this->gridData[$this->editing])) {
            $this->editValue = match ($this->editTurno) {
                'NOCHE' => $this->configHoraEntradaNoche,
                'DÍA' => $this->configHoraEntradaDia,
                default => $this->editValue,
            };
            $this->editValueSalida = match ($this->editTurno) {
                'NOCHE' => $this->configHoraSalidaNoche,
                'DÍA' => $this->configHoraSalidaDia,
                default => $this->editValueSalida,
            };
        }
    }

    private function detectarSubTurno($entrada): string
    {
        if (!$entrada) return 'DÍA';
        $parts = explode(':', $entrada);
        return ((int)$parts[0] * 60 + (int)($parts[1] ?? 0)) >= 14 * 60 ? 'NOCHE' : 'DÍA';
    }

    private function detectarTurno($entrada, $salida): string
    {
        if (!$entrada) return 'DÍA';
        if ($salida) {
            $diff = $this->diffHoras($entrada, $salida);
            if ($diff >= 20) return '24H';
        }
        $parts = explode(':', $entrada);
        return ((int)$parts[0] * 60 + (int)($parts[1] ?? 0)) >= 14 * 60 ? 'NOCHE' : 'DÍA';
    }

    private function diffHoras($a, $b): float
    {
        try {
            $e = explode(':', $a);
            $s = explode(':', $b);
            $diff = ((int)$s[0] * 60 + (int)($s[1] ?? 0)) - ((int)$e[0] * 60 + (int)($e[1] ?? 0));
            return ($diff >= 0 ? $diff : $diff + 1440) / 60;
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function calcular($entrada, $salida, $turno): array
    {
        $limBueno = $this->configLimBueno;
        $limRegular = $this->configLimRegular;

        $horaEntradaRef = $turno === 'NOCHE' ? $this->configHoraEntradaNoche : $this->configHoraEntradaDia;
        $horaSalidaRef = $turno === 'NOCHE' ? $this->configHoraSalidaNoche : $this->configHoraSalidaDia;

        $horas = 0;
        $tardanza = 0;
        $etiqueta = 'SIN REGISTRO';

        if (!$entrada) return [$horas, $tardanza, $etiqueta];

        try {
            $pt = explode(':', $entrada);
            $entMin = (int)$pt[0] * 60 + (int)($pt[1] ?? 0);

            if ($salida) {
                $horas = round($this->diffHoras($entrada, $salida), 1);
            } else {
                $ref = explode(':', $horaSalidaRef);
                $horas = round((((int)$ref[0] * 60 + (int)$ref[1]) - $entMin) / 60, 1);
            }

            if ($turno === 'DÍA') {
                $refEnt = explode(':', $horaEntradaRef);
                $refEntMin = (int)$refEnt[0] * 60 + (int)$refEnt[1];
                $tardanza = max(0, $entMin - $refEntMin);
                if ($tardanza <= $limBueno) $etiqueta = 'BUENO';
                elseif ($tardanza <= $limRegular) $etiqueta = 'REGULAR';
                else $etiqueta = 'MALO';
            } else {
                $etiqueta = $turno;
            }
        } catch (\Exception $e) {}

        return [$horas, $tardanza, $etiqueta];
    }

    public function incDias() { $this->editDiasCubre = min(3, $this->editDiasCubre + 1); }
    public function decDias() { $this->editDiasCubre = max(2, $this->editDiasCubre - 1); }

    public function cycleCell($personaId, $fecha)
    {
        $key = $personaId . '_' . $fecha;
        $reg = $this->gridData[$key] ?? null;
        $currentTurno = $reg['turno'] ?? null;

        $persona = collect($this->personal)->firstWhere('id', (int)$personaId);
        if (!$persona) return;

        if (!$currentTurno || in_array($currentTurno, ['DÍA', 'NOCHE', '24H', '36H'])) {
            $this->guardarRegistro($personaId, $persona['nombre'], $fecha, '', '', 'FALTA', 0, 0, 'FALTA');
        } elseif ($currentTurno === 'FALTA') {
            $this->guardarRegistro($personaId, $persona['nombre'], $fecha, '', '', 'DESCANSO', 0, 0, 'DESCANSO');
        } else {
            $this->guardarRegistro($personaId, $persona['nombre'], $fecha, '', '', 'DÍA', 0, 0, 'SIN REGISTRO');
        }

        $this->loadGrid();
    }

    public function deleteCell()
    {
        if (!$this->editing) return;
        $parts = explode('_', $this->editing);
        $personaId = (int)$parts[0];
        $fecha = substr($this->editing, strlen($parts[0]) + 1);

        AsistenciaModel::where('persona_id', $personaId)
            ->where('fecha', $fecha)
            ->delete();

        $this->cancelEdit();
        $this->loadGrid();
        $this->dispatch('notify', message: 'Registro eliminado.', type: 'success');
    }

    public function cancelEdit()
    {
        $this->editing = null;
        $this->editValue = '';
        $this->editValueSalida = '';
        $this->editTipo = 'ASISTIÓ';
        $this->editTurno = 'DÍA';
        $this->editDiasCubre = 2;
        $this->editFechaFin = '';
        $this->showHoras = false;
        $this->editLockSalida = false;
        $this->editLockEntrada = false;
    }

    public function handleImport($rows)
    {
        $result = \App\Imports\AsistenciaImport::insert($rows ?? []);
        $msg = $result['inserted'] . ' registro(s) importados correctamente.';
        if (!empty($result['errors'])) {
            $msg .= ' Errores: ' . implode(' | ', $result['errors']);
        }
        $this->dispatch('notify', message: $msg, type: empty($result['errors']) ? 'success' : 'warning');
        $this->loadGrid();
    }

    public function guardarMes()
    {
        if ($this->editing) $this->saveCell();
        $this->dispatch('notify', message: 'Asistencia del mes guardada.', type: 'success');
    }

    public function render()
    {
        return view('livewire.olimpo.asistencia')
            ->layout('layouts.olimpo', ['title' => 'Asistencia Mensual']);
    }
}
