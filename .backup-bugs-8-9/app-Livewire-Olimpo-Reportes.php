<?php

namespace App\Livewire\Olimpo;

use Livewire\Component;
use App\Models\Ocurrencia;
use App\Models\TipoOcurrencia;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OcurrenciasExport;
use Illuminate\Support\Facades\DB;

class Reportes extends Component
{
    public $fechaDesde = '';
    public $fechaHasta = '';
    public $tipoFiltro = 'Todos';
    public $log = [];
    public $tipos = [];

    protected $listeners = ['panelChanged' => 'loadData'];

    public function mount()
    {
        $this->fechaHasta = now()->format('d/m/Y');
        $this->loadData();
    }

    public function loadData()
    {
        $this->tipos = collect(TipoOcurrencia::activos()->pluck('nombre')->toArray())->prepend('Todos')->toArray();
    }

    public function exportarOcurrenciasPDF()
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
        $rows = $this->getFilteredOcurrencias();
        if (empty($rows)) {
            $this->dispatch('notify', message: 'No hay datos para exportar.', type: 'warning');
            return;
        }
        $pdf = Pdf::loadView('exports.ocurrencias-pdf', [
            'rows' => $rows,
            'filtro' => $this->filtroInfo(),
        ]);
        $filename = 'Ocurrencias_' . now()->format('Ymd') . '.pdf';
        $this->addLog('PDF de Ocurrencias generado: ' . $filename);
        return response()->streamDownload(fn() => print($pdf->output()), $filename);
    }

    public function exportarOcurrenciasExcel()
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
        $rows = $this->getFilteredOcurrencias();
        if (empty($rows)) {
            $this->dispatch('notify', message: 'No hay datos para exportar.', type: 'warning');
            return;
        }
        $filename = 'Ocurrencias_' . now()->format('Ymd') . '.xlsx';
        $this->addLog('Excel de Ocurrencias generado: ' . $filename);
        return Excel::download(new OcurrenciasExport($rows), $filename);
    }

    public function exportarAsistenciaPDF()
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
        $fecha = $this->fechaHasta ?: now()->format('d/m/Y');
        $rows = DB::table('asistencia')->where('fecha', $fecha)->get()->map(fn($r) => (array)$r)->toArray();
        if (empty($rows)) {
            $this->dispatch('notify', message: 'No hay datos de asistencia para esa fecha.', type: 'warning');
            return;
        }
        $pdf = Pdf::loadView('exports.asistencia-pdf', ['rows' => $rows, 'fecha' => $fecha]);
        $filename = 'Asistencia_' . $fecha . '.pdf';
        $this->addLog('PDF de Asistencia generado: ' . $filename);
        return response()->streamDownload(fn() => print($pdf->output()), $filename);
    }

    public function exportarAsistenciaExcel()
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
        $fecha = $this->fechaHasta ?: now()->format('d/m/Y');
        $rows = DB::table('asistencia')->where('fecha', $fecha)->get()->map(fn($r) => (array)$r)->toArray();
        if (empty($rows)) {
            $this->dispatch('notify', message: 'No hay datos de asistencia para esa fecha.', type: 'warning');
            return;
        }
        $filename = 'Asistencia_' . $fecha . '.xlsx';
        $this->addLog('Excel de Asistencia generado: ' . $filename);
        return Excel::download(new \App\Exports\AsistenciaExport($rows), $filename);
    }

    private function getFilteredOcurrencias()
    {
        $query = Ocurrencia::query()
            ->leftJoin('personal', 'ocurrencias.persona_nombre', '=', 'personal.nombre')
            ->select('ocurrencias.*', 'personal.cargo as persona_cargo');
        if ($this->fechaDesde) $query->whereRaw("(SUBSTR(ocurrencias.fecha,7,4)||SUBSTR(ocurrencias.fecha,4,2)||SUBSTR(ocurrencias.fecha,1,2)) >= ?", [$this->sortable($this->fechaDesde)]);
        if ($this->fechaHasta) $query->whereRaw("(SUBSTR(ocurrencias.fecha,7,4)||SUBSTR(ocurrencias.fecha,4,2)||SUBSTR(ocurrencias.fecha,1,2)) <= ?", [$this->sortable($this->fechaHasta)]);
        if ($this->tipoFiltro !== 'Todos') $query->where('ocurrencias.tipo', $this->tipoFiltro);
        return $query->orderBy('ocurrencias.fecha', 'desc')->get()->toArray();
    }

    private function sortable($d)
    {
        if (!preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $d)) {
            return '00000000';
        }
        $parts = explode('/', $d);
        return $parts[2] . $parts[1] . $parts[0];
    }

    private function filtroInfo()
    {
        return "Desde: " . ($this->fechaDesde ?: '-') . " - Hasta: " . ($this->fechaHasta ?: '-') . " - Tipo: " . ($this->tipoFiltro ?: 'Todos');
    }

    private function addLog($msg)
    {
        $this->log[] = '[' . now()->format('H:i:s') . '] ' . $msg;
    }

    public function render()
    {
        return view('livewire.olimpo.reportes')
            ->layout('layouts.olimpo', ['title' => 'Reportes y Exportación']);
    }
}