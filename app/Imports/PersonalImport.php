<?php

namespace App\Imports;

use App\Models\Personal;

class PersonalImport
{
    public static function insert(array $rows): array
    {
        $inserted = 0;
        $errors = [];

        foreach ($rows as $row) {
            $data = $row['data'];

            try {
                if (!empty($data['cargo'])) {
                    $cargo = \App\Models\Cargo::where('nombre', $data['cargo'])->first();
                    if ($cargo) {
                        $data['cargo_id'] = $cargo->id;
                    }
                }
                Personal::create([
                    'nombre' => $data['nombre'],
                    'cargo' => $data['cargo'] ?? '',
                    'cargo_id' => $data['cargo_id'] ?? null,
                    'departamento' => $data['departamento'] ?? '',
                    'documento' => $data['documento'] ?? '',
                    'telefono' => $data['telefono'] ?? '',
                    'email' => $data['email'] ?? '',
                    'estado' => $data['estado'] ?? 'ACTIVO',
                    'hora_entrada' => $data['hora_entrada'] ?? null,
                    'hora_salida' => $data['hora_salida'] ?? null,
                ]);
                $inserted++;
            } catch (\Exception $e) {
                $errors[] = 'Fila ' . ($row['_index'] ?? '?') . ': ' . $e->getMessage();
            }
        }

        return ['inserted' => $inserted, 'errors' => $errors];
    }
}
