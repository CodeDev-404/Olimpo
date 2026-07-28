<?php

namespace App\Livewire\Olimpo;

use Livewire\Component;
use App\Models\Cumpleano;
use App\Models\Ocurrencia;
use App\Models\Asistencia;
use App\Models\ControlVehiculo;
use App\Models\Combustible;
use App\Models\Personal;
use App\Models\TipoOcurrencia;
use Illuminate\Support\Facades\DB;

class Dashboard extends Component
{
    public $cumpleanos = [];
    public $proximosCumpleanos = [];
    public $kpis = [];
    public $ocurrenciasHoy = [];
    public $ocurrenciasRecientes = [];
    public $asistenciaHoy = [];
    public $personalOnline = [];
    public $notificaciones = [];
    public $chartTipoData = null;
    public $chartSemanalData = null;
    public $calendarioMes = [];
    public $calendarioEventos = [];

    public function mount()
    {
        $this->loadCumpleanos();
        $this->loadKpis();
        $this->loadOcurrencias();
        $this->loadAsistenciaHoy();
        $this->loadPersonalOnline();
        $this->loadNotificaciones();
        $this->loadChartData();
        $this->loadCalendario();
    }

    public function loadCumpleanos()
    {
        $cacheKey = 'dash_cumpleanos_' . now()->format('Ymd');
        $data = cache()->remember($cacheKey, 300, function () {
            $hoy = now()->format('d/m');
            $cumpleanos = Cumpleano::whereRaw("SUBSTR(fecha, 1, 5) = ?", [$hoy])
                ->orderBy('nombre')
                ->get()
                ->map(fn($c) => [
                    'id' => $c->id,
                    'nombre' => t($c->nombre),
                    'parentesco' => t($c->parentesco ?? ''),
                    'detalles' => t($c->detalles),
                    'fecha' => $c->fecha,
                    'dni' => $c->dni,
                ])
                ->toArray();

            $prox = now()->addDay();
            $proxEnd = now()->addDays(7);
            $fechas = [];
            for ($d = $prox; $d <= $proxEnd; $d->addDay()) {
                $fechas[] = $d->format('d/m');
            }
            $proximosCumpleanos = Cumpleano::whereIn(DB::raw("SUBSTR(fecha, 1, 5)"), $fechas)
                ->orderBy(DB::raw("SUBSTR(fecha, 1, 5)"))
                ->get()
                ->map(fn($c) => [
                    'nombre' => t($c->nombre),
                    'parentesco' => t($c->parentesco ?? ''),
                    'fecha' => $c->fecha,
                ])
                ->toArray();

            return compact('cumpleanos', 'proximosCumpleanos');
        });

        $this->cumpleanos = $data['cumpleanos'];
        $this->proximosCumpleanos = $data['proximosCumpleanos'];
    }

    public function loadKpis()
    {
        $cacheKey = 'dash_kpis_' . now()->format('YmdH');
        $this->kpis = cache()->remember($cacheKey, 60, function () {
            $hoy = now()->format('d/m/Y');
            $mes = now()->month;
            $anio = now()->year;

            $totalPersonal = Personal::count();
            $personalActivo = Personal::where('estado', 'ACTIVO')->count();
            $asistenciaHoy = Asistencia::where('fecha', $hoy)->count();

            return [
                'personal_activo' => $personalActivo,
                'personal_total' => $totalPersonal,
                'personal_pct' => $totalPersonal > 0 ? round($personalActivo / $totalPersonal * 100) : 0,
                'ocurrencias_hoy' => Ocurrencia::where('fecha', $hoy)->count(),
                'ocurrencias_mes' => Ocurrencia::where('mes', $mes)->where('anio', $anio)->count(),
                'vehiculos_uso' => ControlVehiculo::where('fecha', $hoy)->whereNull('hora_ingreso')->count(),
                'vehiculos_total' => ControlVehiculo::select('placa')->distinct()->count(),
                'combustible_mes' => Combustible::whereMonth('created_at', $mes)->whereYear('created_at', $anio)->sum('total'),
                'asistencia_hoy' => $asistenciaHoy,
                'tardanzas_hoy' => Asistencia::where('fecha', $hoy)->where('tardanza_min', '>', 0)->count(),
                'ausentes_hoy' => $personalActivo - $asistenciaHoy,
            ];
        });
    }

    public function loadOcurrencias()
    {
        $cacheKey = 'dash_oc_' . now()->format('Ymd');
        $data = cache()->remember($cacheKey, 60, function () {
            $hoy = now()->format('d/m/Y');

            $ocurrenciasHoy = Ocurrencia::where('fecha', $hoy)
                ->orderBy('hora_ingreso', 'desc')
                ->orderBy('id', 'desc')
                ->limit(8)
                ->get()
                ->map(fn($o) => [
                    'id' => $o->id,
                    'persona' => t($o->persona_nombre),
                    'tipo' => t($o->tipo),
                    'hora_ingreso' => $o->hora_ingreso,
                    'hora_salida' => $o->hora_salida,
                    'vehiculo' => $o->vehiculo,
                    'destino' => t($o->destino),
                    'turno' => $o->turno,
                ])
                ->toArray();

            $ocurrenciasRecientes = [];
            if (count($ocurrenciasHoy) === 0) {
                $ocurrenciasRecientes = Ocurrencia::orderBy('fecha', 'desc')
                    ->orderBy('id', 'desc')
                    ->limit(8)
                    ->get()
                    ->map(fn($o) => [
                        'id' => $o->id,
                        'persona' => t($o->persona_nombre),
                        'tipo' => t($o->tipo),
                        'hora_ingreso' => $o->hora_ingreso,
                        'hora_salida' => $o->hora_salida,
                        'vehiculo' => $o->vehiculo,
                        'destino' => t($o->destino),
                        'turno' => $o->turno,
                        'fecha' => $o->fecha,
                    ])
                    ->toArray();
            }

            return compact('ocurrenciasHoy', 'ocurrenciasRecientes');
        });

        $this->ocurrenciasHoy = $data['ocurrenciasHoy'];
        $this->ocurrenciasRecientes = $data['ocurrenciasRecientes'];
    }

    public function loadAsistenciaHoy()
    {
        $cacheKey = 'dash_asistencia_' . now()->format('Ymd');
        $this->asistenciaHoy = cache()->remember($cacheKey, 60, function () {
            $hoy = now()->format('d/m/Y');
            return Asistencia::where('fecha', $hoy)
                ->orderBy('hora_entrada')
                ->get()
                ->map(fn($a) => [
                    'persona' => t($a->persona_nombre),
                    'hora_entrada' => $a->hora_entrada,
                    'tardanza' => $a->tardanza_min,
                    'horas' => $a->horas_trabajadas,
                    'etiqueta' => $a->etiqueta,
                ])
                ->toArray();
        });
    }

    public function loadPersonalOnline()
    {
        $cacheKey = 'dash_online_' . now()->format('YmdH');
        $this->personalOnline = cache()->remember($cacheKey, 60, function () {
            $hoy = now()->format('d/m/Y');
            $online = Ocurrencia::where('fecha', $hoy)
                ->where(function($q) {
                    $q->whereNull('hora_salida')->orWhere('hora_salida', '');
                })
                ->whereNotNull('hora_ingreso')
                ->where('hora_ingreso', '!=', '')
                ->get()
                ->unique('persona_nombre')
                ->values()
                ->map(fn($o) => [
                    'name' => t($o->persona_nombre),
                    'status' => $o->destino ? 'En ' . t($o->destino) : ($o->vehiculo ? 'En ruta' : 'En oficina'),
                    'online' => true,
                ])
                ->toArray();

            $activos = Personal::where('estado', 'ACTIVO')
                ->whereNotIn('id', function($q) use ($hoy) {
                    $q->select('persona_id')
                      ->from('ocurrencias')
                      ->where('fecha', $hoy)
                      ->whereNotNull('hora_ingreso')
                      ->where('hora_ingreso', '!=', '')
                      ->where(function($sub) {
                          $sub->whereNull('hora_salida')->orWhere('hora_salida', '');
                      });
                })
                ->limit(5)
                ->get()
                ->map(fn($p) => [
                    'name' => t($p->nombre),
                    'status' => '',
                    'online' => false,
                ])
                ->toArray();

            return array_merge($online, $activos);
        });
    }

    public function loadNotificaciones()
    {
        $hoy = now()->format('d/m/Y');
        $this->notificaciones = [];

        $ocurrenciasSinCerrar = Ocurrencia::where('fecha', $hoy)
            ->where(function($q) {
                $q->whereNull('hora_salida')->orWhere('hora_salida', '');
            })
            ->count();
        if ($ocurrenciasSinCerrar > 0) {
            $this->notificaciones[] = [
                'tipo' => 'danger',
                'titulo' => "{$ocurrenciasSinCerrar} ocurrencias sin cerrar",
                'desc' => 'Personal aún en comisión sin registrar salida',
            ];
        }

        if (count($this->proximosCumpleanos) > 0) {
            $this->notificaciones[] = [
                'tipo' => 'warning',
                'titulo' => 'Cumpleaños de ' . $this->proximosCumpleanos[0]['nombre'],
                'desc' => $this->proximosCumpleanos[0]['fecha'] . ' — Próximamente',
            ];
        }

        $pendientesPalm = Personal::where('estado', 'ACTIVO')
            ->whereNotIn('id', function($q) use ($hoy) {
                $q->select('persona_id')
                  ->from('asistencia')
                  ->where('fecha', $hoy);
            })
            ->count();
        if ($pendientesPalm > 0) {
            $this->notificaciones[] = [
                'tipo' => 'info',
                'titulo' => "{$pendientesPalm} empleados sin registro",
                'desc' => 'Personal activo sin asistencia registrada hoy',
            ];
        }

        $tardanzas = $this->kpis['tardanzas_hoy'] ?? 0;
        if ($tardanzas > 0) {
            $this->notificaciones[] = [
                'tipo' => 'danger',
                'titulo' => "{$tardanzas} tardanzas hoy",
                'desc' => 'Personal que ingresó después de su hora',
            ];
        }
    }

    public function loadChartData()
    {
        $cacheKey = 'dash_chart_' . now()->format('Ym');
        $data = cache()->remember($cacheKey, 300, function () {
            $mes = now()->month;
            $anio = now()->year;

            $tipos = TipoOcurrencia::where('activo', true)->pluck('nombre', 'id');
            $data = DB::table('ocurrencias')
                ->select('tipo_id', DB::raw('COUNT(*) as total'))
                ->where('mes', $mes)
                ->where('anio', $anio)
                ->whereNotNull('tipo_id')
                ->groupBy('tipo_id')
                ->get()
                ->keyBy('tipo_id');

            $labels = [];
            $values = [];
            foreach ($tipos as $id => $nombre) {
                if (isset($data[$id])) {
                    $labels[] = $nombre;
                    $values[] = (int) $data[$id]->total;
                }
            }

            $chartTipoData = null;
            if (!empty($labels)) {
                $palette = ['#6366f1','#a855f7','#ec4899','#f43f5e','#f97316','#eab308','#22c55e','#14b8a6','#06b6d4','#3b82f6'];
                $chartTipoData = [
                    'labels' => $labels,
                    'data' => $values,
                    'colors' => array_slice($palette, 0, count($labels)),
                ];
            }

            $dates = [];
            $dayLabels = [];
            for ($i = 6; $i >= 0; $i--) {
                $d = now()->subDays($i);
                $f = $d->format('d/m/Y');
                $dates[] = $f;
                $dayLabels[$f] = ucfirst($d->isoFormat('dddd'));
            }

            $counts = Ocurrencia::whereIn('fecha', $dates)
                ->groupBy('fecha')
                ->pluck(DB::raw('COUNT(*) as total'), 'fecha')
                ->toArray();

            $chartSemanalData = [];
            foreach ($dates as $f) {
                $chartSemanalData[] = [
                    'label' => $dayLabels[$f],
                    'fecha' => $f,
                    'total' => (int) ($counts[$f] ?? 0),
                ];
            }

            return compact('chartTipoData', 'chartSemanalData');
        });

        $this->chartTipoData = $data['chartTipoData'];
        $this->chartSemanalData = $data['chartSemanalData'];
    }

    public function loadCalendario()
    {
        $cacheKey = 'dash_cal_' . now()->format('Ym');
        $data = cache()->remember($cacheKey, 300, function () {
            $now = now();
            $year = $now->year;
            $month = $now->month;
            $daysInMonth = $now->daysInMonth;
            $firstDayOfWeek = now()->startOfMonth()->dayOfWeek;

            $dias = [];
            for ($i = 0; $i < $firstDayOfWeek; $i++) {
                $dias[] = null;
            }
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $dias[] = $d;
            }

            $fullDates = [];
            $shortDates = [];
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $fullDates[] = str_pad($d, 2, '0', STR_PAD_LEFT) . '/' . str_pad($month, 2, '0', STR_PAD_LEFT) . '/' . $year;
                $shortDates[] = str_pad($d, 2, '0', STR_PAD_LEFT) . '/' . str_pad($month, 2, '0', STR_PAD_LEFT);
            }

            $ocCounts = Ocurrencia::whereIn('fecha', $fullDates)
                ->groupBy('fecha')
                ->pluck(DB::raw('COUNT(*) as total'), 'fecha')
                ->toArray();

            $asCounts = Asistencia::whereIn('fecha', $fullDates)
                ->groupBy('fecha')
                ->pluck(DB::raw('COUNT(*) as total'), 'fecha')
                ->toArray();

            $bdCounts = Cumpleano::whereIn(DB::raw("SUBSTR(fecha, 1, 5)"), $shortDates)
                ->groupBy(DB::raw("SUBSTR(fecha, 1, 5)"))
                ->pluck(DB::raw('COUNT(*) as total'), DB::raw("SUBSTR(fecha, 1, 5)"))
                ->toArray();

            $eventos = [];
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $events = [];
                $fecha = $fullDates[$d - 1];
                $fechaCorta = $shortDates[$d - 1];

                $ocCount = (int) ($ocCounts[$fecha] ?? 0);
                if ($ocCount > 0) {
                    $events[] = ['type' => 'ocurrencia', 'count' => $ocCount];
                }

                $asCount = (int) ($asCounts[$fecha] ?? 0);
                if ($asCount > 0) {
                    $events[] = ['type' => 'asistencia', 'count' => $asCount];
                }

                $birthday = (int) ($bdCounts[$fechaCorta] ?? 0);
                if ($birthday > 0) {
                    $events[] = ['type' => 'cumple', 'count' => $birthday];
                }

                if (!empty($events)) {
                    $eventos[$d] = $events;
                }
            }

            return compact('dias', 'eventos');
        });

        $this->calendarioMes = $data['dias'];
        $this->calendarioEventos = $data['eventos'];
    }

    public function refreshData()
    {
        $this->loadCumpleanos();
        $this->loadKpis();
        $this->loadOcurrencias();
        $this->loadAsistenciaHoy();
        $this->loadPersonalOnline();
        $this->loadNotificaciones();
        $this->loadChartData();
        $this->loadCalendario();
    }

    public function render()
    {
        return view('livewire.olimpo.dashboard')->layout('layouts.olimpo', ['title' => 'Dashboard']);
    }
}
