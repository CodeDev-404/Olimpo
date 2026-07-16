<?php

namespace App\Imports;

use App\Models\Asistencia;
use App\Models\Personal;

class AsistenciaImport
{
    public static function insert(array $rows): array
    {
        $inserted = 0;
        $errors = [];

        foreach ($rows as $row) {
            $data = $row['data'];

            try {
                $personaId = Personal::where('nombre', $data['persona_nombre'])->value('id');
                if (!$personaId) {
                    $errors[] = "Persona no encontrada: {$data['persona_nombre']}";
                    continue;
                }

                Asistencia::create([
                    'persona_id' => $personaId,
                    'persona_nombre' => $data['persona_nombre'],
                    'fecha' => $data['fecha'],
                    'hora_entrada' => $data['hora_entrada'] ?? null,
                    'turno' => $data['turno'] ?? 'DÍA',
                    'hora_salida' => $data['hora_salida'] ?? null,
                    'tardanza_min' => $data['tardanza_min'] ?? 0,
                    'horas_trabajadas' => $data['horas_trabajadas'] ?? 0,
                    'etiqueta' => $data['etiqueta'] ?? 'BUENO',
                ]);
                $inserted++;
            } catch (\Exception $e) {
                $errors[] = 'Fila ' . ($row['_index'] ?? '?') . ': ' . $e->getMessage();
            }
        }

        return ['inserted' => $inserted, 'errors' => $errors];
    }
}
