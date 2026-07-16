<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AsistenciaExport implements FromArray, WithHeadings, WithStyles
{
    protected $rows;

    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public function array(): array
    {
        $data = [];
        foreach ($this->rows as $a) {
            $data[] = [
                $a['persona_nombre'],
                $a['fecha'],
                $a['hora_entrada'],
                $a['hora_salida'],
                $a['tardanza_min'],
                $a['horas_trabajadas'] . 'h',
                $a['etiqueta'],
            ];
        }
        return $data;
    }

    public function headings(): array
    {
        return ['NOMBRE', 'FECHA', 'H. ENTRADA', 'H. SALIDA', 'TARDANZA (min)', 'H. TRABAJADAS', 'ETIQUETA'];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:G1')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1C1C2E']],
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
        ]);
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        return [];
    }
}
