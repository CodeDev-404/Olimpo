<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Personal;
use App\Models\TipoOcurrencia;
use App\Models\Ocurrencia;
use App\Models\Cargo;
use App\Models\Camioneta;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@olimpo.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        // Demo user
        User::create([
            'name' => 'Usuario Demo',
            'email' => 'user@olimpo.com',
            'password' => Hash::make('user123'),
            'role' => 'user',
        ]);

        // Tipos de ocurrencia
        $tipos = [
            ['nombre' => 'Tardanza', 'nivel' => 'Leve', 'color' => '#F0C040', 'activo' => true],
            ['nombre' => 'Ausencia', 'nivel' => 'Moderado', 'color' => '#E07820', 'activo' => true],
            ['nombre' => 'Falta grave', 'nivel' => 'Grave', 'color' => '#C0392B', 'activo' => true],
            ['nombre' => 'Incidente', 'nivel' => 'Moderado', 'color' => '#E07820', 'activo' => true],
            ['nombre' => 'Daño a equipo', 'nivel' => 'Grave', 'color' => '#C0392B', 'activo' => true],
            ['nombre' => 'Falta de equipo', 'nivel' => 'Moderado', 'color' => '#E07820', 'activo' => true],
            ['nombre' => 'Conducta', 'nivel' => 'Grave', 'color' => '#C0392B', 'activo' => true],
            ['nombre' => 'Otro', 'nivel' => 'Leve', 'color' => '#1FAE74', 'activo' => true],
        ];
        foreach ($tipos as $t) {
            TipoOcurrencia::create($t);
        }

        // Personal de ejemplo
        $personas = [
            ['nombre' => 'Ana García López', 'cargo' => 'Analista', 'departamento' => 'Operaciones'],
            ['nombre' => 'Carlos Mendoza Ríos', 'cargo' => 'Supervisor', 'departamento' => 'Logística'],
            ['nombre' => 'Diana Torres Vega', 'cargo' => 'Técnico', 'departamento' => 'Mantenimiento'],
            ['nombre' => 'Eduardo Silva Cruz', 'cargo' => 'Coordinador', 'departamento' => 'RRHH'],
            ['nombre' => 'Fernanda Ruiz Mora', 'cargo' => 'Especialista', 'departamento' => 'TI'],
        ];
        foreach ($personas as $p) {
            Personal::create($p);
        }

        // Ocurrencias de ejemplo
        $hoy = now()->format('d/m/Y');
        Ocurrencia::create([
            'fecha' => $hoy, 'hora_ingreso' => '08:45', 'hora_salida' => '17:00',
            'persona_nombre' => 'Carlos Mendoza Ríos', 'tipo' => 'Tardanza',
            'detalles' => 'Llegó 45 minutos tarde sin justificación previa.',
            'observacion' => 'Llamado de atención verbal', 'mes' => now()->month, 'anio' => now()->year,
        ]);
        Ocurrencia::create([
            'fecha' => $hoy, 'hora_ingreso' => '09:30', 'hora_salida' => '17:00',
            'persona_nombre' => 'Ana García López', 'tipo' => 'Ausencia',
            'detalles' => 'No se presentó al turno asignado.',
            'observacion' => 'Falta sin aviso previo', 'mes' => now()->month, 'anio' => now()->year,
        ]);
        Ocurrencia::create([
            'fecha' => $hoy, 'hora_ingreso' => '08:05', 'hora_salida' => '17:00',
            'persona_nombre' => 'Eduardo Silva Cruz', 'tipo' => 'Incidente',
            'detalles' => 'Accidente menor con herramienta manual, sin lesiones.',
            'observacion' => 'Reporte a SSGG generado', 'mes' => now()->month, 'anio' => now()->year,
        ]);

        // Configuración por defecto
        $config = [
            'hora_entrada_std' => '08:00', 'hora_salida_std' => '17:00',
            'almuerzo_min' => '60', 'limite_bueno_min' => '5',
            'limite_regular_min' => '20',
        ];
        foreach ($config as $clave => $valor) {
            DB::table('configuracion')->insert(['clave' => $clave, 'valor' => $valor]);
        }
    }
}
