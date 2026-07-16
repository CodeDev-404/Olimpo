<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create Cargo records from existing cargo strings in personal table
        $existing = DB::table('personal')->where('cargo', '!=', '')->distinct()->pluck('cargo');
        foreach ($existing as $name) {
            DB::table('cargos')->updateOrInsert(['nombre' => $name], ['nombre' => $name, 'created_at' => now(), 'updated_at' => now()]);
        }

        // Add cargo_id column
        Schema::table('personal', function (Blueprint $table) {
            $table->foreignId('cargo_id')->nullable()->constrained('cargos')->nullOnDelete()->after('cargo');
        });

        // Set cargo_id from cargo name
        $personas = DB::table('personal')->where('cargo', '!=', '')->get();
        foreach ($personas as $p) {
            $cargo = DB::table('cargos')->where('nombre', $p->cargo)->first();
            if ($cargo) {
                DB::table('personal')->where('id', $p->id)->update(['cargo_id' => $cargo->id]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('personal', function (Blueprint $table) {
            $table->dropForeign(['cargo_id']);
            $table->dropColumn('cargo_id');
        });
    }
};
