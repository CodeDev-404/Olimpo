<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cargos', function (Blueprint $table) {
            $table->integer('orden')->default(0)->after('grupo');
        });

        DB::table('cargos')->where('nombre', 'CHOFER')->update(['grupo' => 'CHOFERES', 'orden' => 1]);

        DB::table('cargos')->where('nombre', 'ENCARGADO')->update(['grupo' => 'OLIMPO', 'orden' => 1]);
        DB::table('cargos')->where('nombre', 'VIGILANTE')->update(['grupo' => 'OLIMPO', 'orden' => 2]);
        DB::table('cargos')->where('nombre', 'AUXILIAR')->update(['grupo' => 'OLIMPO', 'orden' => 3]);

        DB::table('cargos')->where('nombre', 'SECRETARIA')->update(['grupo' => 'COCINA', 'orden' => 1]);
        DB::table('cargos')->where('nombre', 'MAYORDOMO')->update(['grupo' => 'COCINA', 'orden' => 2]);
        DB::table('cargos')->where('nombre', 'LAVANDERIA')->update(['grupo' => 'COCINA', 'orden' => 3]);
        DB::table('cargos')->where('nombre', 'COCINA')->update(['grupo' => 'COCINA', 'orden' => 4]);

        DB::table('cargos')->where('nombre', 'JARDINERO')->update(['grupo' => 'MANTENIMIENTO', 'orden' => 1]);
        DB::table('cargos')->where('nombre', 'MANTENIMIENTO')->update(['grupo' => 'MANTENIMIENTO', 'orden' => 2]);
    }

    public function down(): void
    {
        Schema::table('cargos', function (Blueprint $table) {
            $table->dropColumn('orden');
        });
    }
};
