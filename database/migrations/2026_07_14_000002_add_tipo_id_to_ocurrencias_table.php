<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create TipoOcurrencia records from existing tipo strings
        $existing = DB::table('ocurrencias')->where('tipo', '!=', '')->distinct()->pluck('tipo');
        foreach ($existing as $name) {
            DB::table('tipos_ocurrencia')->updateOrInsert(
                ['nombre' => $name],
                ['nombre' => $name, 'created_at' => now(), 'updated_at' => now()]
            );
        }

        // Add tipo_id column
        Schema::table('ocurrencias', function (Blueprint $table) {
            $table->foreignId('tipo_id')->nullable()->after('tipo')->constrained('tipos_ocurrencia')->nullOnDelete();
        });

        // Backfill tipo_id from tipo string
        $rows = DB::table('ocurrencias')->where('tipo', '!=', '')->get();
        foreach ($rows as $r) {
            $tipo = DB::table('tipos_ocurrencia')->where('nombre', $r->tipo)->first();
            if ($tipo) {
                DB::table('ocurrencias')->where('id', $r->id)->update(['tipo_id' => $tipo->id]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('ocurrencias', function (Blueprint $table) {
            $table->dropForeign(['tipo_id']);
            $table->dropColumn('tipo_id');
        });
    }
};
