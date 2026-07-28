<?php

namespace App\Imports;

use App\Models\ControlVehiculo;

class ControlVehiculosImport
{
    public static function insert(array $rows): array
    {
        $inserted = 0;
        $errors = [];

        foreach ($rows as $row) {
            $data = $row['data'];

            try {
                ControlVehiculo::create([
                    'fecha' => $data['fecha'],
                    'chofer' => $data['chofer'],
                    'placa' => $data['placa'] ?? '',
                    'marca' => $data['marca'] ?? '',
                    'modelo' => $data['modelo'] ?? '',
                    'clase' => $data['clase'] ?? '',
                    'hora_salida' => $data['hora_salida'] ?? '',
                    'km_salida' => $data['km_salida'] ?? '',
                    'hora_ingreso' => $data['hora_ingreso'] ?? '',
                    'km_ingreso' => $data['km_ingreso'] ?? '',
                    'observacion' => $data['observacion'] ?? '',
                ]);
                $inserted++;
            } catch (\Exception $e) {
                $errors[] = 'Fila ' . ($row['_index'] ?? '?') . ': ' . $e->getMessage();
            }
        }

        return ['inserted' => $inserted, 'errors' => $errors];
    }
}
