<?php

namespace App\Imports;

use App\Models\Cumpleano;
use Carbon\Carbon;

class CumpleanosImport
{
    private static function excelSerialToDdMm($serial): ?string
    {
        // Excel serial: days since 1900-01-01 (with 1900 leap year bug)
        // For dates >= 1900-03-01, subtract 2 to correct
        $serial = (int) $serial;
        if ($serial < 60) return null; // too early, probably not a date
        $unix = ($serial - 25569) * 86400; // 25569 = days 1970-01-01 to 1899-12-30
        return date('d/m', $unix);
    }

    public static function insert(array $rows): array
    {
        $inserted = 0;
        $errors = [];

        foreach ($rows as $row) {
            $data = $row['data'];

            // Convert Excel serial number to DD/MM if needed
            if (isset($data['fecha']) && ctype_digit((string)$data['fecha'])) {
                $converted = self::excelSerialToDdMm($data['fecha']);
                if ($converted) {
                    $data['fecha'] = $converted;
                }
            }

            try {
                $recordatorioActivo = isset($data['recordatorio_activo']) ? filter_var($data['recordatorio_activo'], FILTER_VALIDATE_BOOLEAN) : false;
                $recordatorioHora = $data['recordatorio_hora'] ?? '07:30';
                if ($recordatorioActivo && !preg_match('/^\d{2}:\d{2}$/', $recordatorioHora)) {
                    $recordatorioHora = '07:30';
                }

                Cumpleano::create([
                    'fecha' => $data['fecha'],
                    'nombre' => $data['nombre'],
                    'parentesco' => $data['parentesco'] ?? '',
                    'detalles' => $data['detalles'] ?? '',
                    'recordatorio_activo' => $recordatorioActivo,
                    'recordatorio_hora' => $recordatorioHora,
                ]);
                $inserted++;
            } catch (\Exception $e) {
                $errors[] = 'Fila ' . ($row['_index'] ?? '?') . ': ' . $e->getMessage();
            }
        }

        return ['inserted' => $inserted, 'errors' => $errors];
    }
}
