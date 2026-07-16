<?php

namespace App\Imports;

use App\Models\Ocurrencia;

class OcurrenciasImport
{
    public static function insert(array $rows): array
    {
        $inserted = 0;
        $errors = [];

        foreach ($rows as $row) {
            $data = $row['data'];

            try {
                if (!empty($data['persona_nombre'])) {
                    $personaId = \App\Models\Personal::where('nombre', $data['persona_nombre'])->value('id');
                    if ($personaId) {
                        $data['persona_id'] = $personaId;
                    }
                }
                Ocurrencia::create([
                    'fecha' => $data['fecha'],
                    'hora_ingreso' => $data['hora_ingreso'] ?? null,
                    'hora_salida' => $data['hora_salida'] ?? null,
                    'persona_nombre' => $data['persona_nombre'],
                    'persona_id' => $data['persona_id'] ?? null,
                    'tipo' => $data['tipo'] ?? null,
                    'otro' => $data['otro'] ?? '',
                    'detalles' => $data['detalles'] ?? '',
                    'observacion' => $data['observacion'] ?? '',
                    'mes' => $data['mes'] ?? now()->month,
                    'anio' => $data['anio'] ?? now()->year,
                ]);
                $inserted++;
            } catch (\Exception $e) {
                $errors[] = 'Fila ' . ($row['_index'] ?? '?') . ': ' . $e->getMessage();
            }
        }

        return ['inserted' => $inserted, 'errors' => $errors];
    }
}
