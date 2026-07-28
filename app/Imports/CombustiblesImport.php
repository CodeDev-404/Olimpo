<?php

namespace App\Imports;

use App\Models\Combustible;

class CombustiblesImport
{
    public static function insert(array $rows): array
    {
        $inserted = 0;
        $errors = [];

        foreach ($rows as $row) {
            $data = $row['data'];

            try {
                $galones = (float) str_replace(',', '', $data['galones']);
                $precioGalon = (float) str_replace(',', '', $data['precio_galon'] ?? 0);
                $total = (float) str_replace(',', '', $data['total'] ?? 0);

                Combustible::create([
                    'fecha' => $data['fecha'],
                    'categoria' => $data['categoria'] ?? '',
                    'clase' => $data['clase'] ?? '',
                    'marca' => $data['marca'] ?? '',
                    'placa' => $data['placa'] ?? '',
                    'modelo' => $data['modelo'] ?? '',
                    'anio' => $data['anio'] ?? '',
                    'color' => $data['color'] ?? '',
                    'conductor' => $data['conductor'] ?? $data['chofer'] ?? '',
                    'kilometraje' => $data['kilometraje'] ?? '',
                    'combustible' => $data['combustible'],
                    'galones' => $galones,
                    'precio_galon' => $precioGalon,
                    'total' => $total,
                ]);
                $inserted++;
            } catch (\Exception $e) {
                $errors[] = 'Fila ' . ($row['_index'] ?? '?') . ': ' . $e->getMessage();
            }
        }

        return ['inserted' => $inserted, 'errors' => $errors];
    }
}
