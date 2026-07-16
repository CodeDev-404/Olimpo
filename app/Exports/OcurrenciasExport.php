<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class OcurrenciasExport implements FromArray, WithHeadings, WithStyles
{
    protected $rows;

    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public function array(): array
    {
        $data = [];
        foreach ($this->rows as $oc) {
            $data[] = [
                $oc['fecha'],
                $oc['hora_ingreso'],
                $oc['hora_salida'],
                $oc['persona_nombre'],
                $oc['detalles'],
                $oc['observacion'],
                $oc['persona_cargo'] ?? '',
                $oc['tipo'],
                $oc['otro'] ?? '',
            ];
        }
        return $data;
    }

    public function headings(): array
    {
        return ['FECHA', 'H. INGRESO', 'H. SALIDA', 'NOMBRE', 'DETALLES', 'OBSERVACIÓN', 'CARGO', 'TIPO', 'OTRO'];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:I1')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1C1C2E']],
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
        ]);

        $sheet->getStyle('A2:I' . (count($this->rows) + 1))->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'CCCCCC']],
            ],
        ]);

        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [];
    }
}
