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

    public static function columnMap(): array
    {
        return [
            'persona_nombre' => 'NOMBRE',
            'fecha' => 'FECHA',
            'hora_entrada' => 'H. ENTRADA',
            'hora_salida' => 'H. SALIDA',
            'tardanza_min' => 'TARDANZA (min)',
            'horas_trabajadas' => 'H. TRABAJADAS',
            'etiqueta' => 'ETIQUETA',
            'turno' => 'TURNO',
        ];
    }

    public function array(): array
    {
        $cols = array_keys(static::columnMap());
        return array_map(fn($row) => array_map(fn($c) => $row[$c] ?? '', $cols), $this->rows);
    }

    public function headings(): array
    {
        return array_values(static::columnMap());
    }

    public function styles(Worksheet $sheet)
    {
        $lastCol = chr(64 + count(static::columnMap()));
        $lastRow = count($this->rows) + 1;

        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1C1C2E']],
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
        ]);

        $sheet->getStyle("A2:{$lastCol}{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'CCCCCC']],
            ],
        ]);

        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [];
    }
}
