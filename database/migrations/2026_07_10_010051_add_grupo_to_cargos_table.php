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
            $table->string('grupo', 20)->default('OLIMPO')->after('nombre');
        });

        DB::table('cargos')->where('nombre', 'CHOFER')->update(['grupo' => 'CHOFERES']);
        DB::table('cargos')->where('nombre', 'ENCARGADO')->update(['grupo' => 'OLIMPO']);
    }

    public function down(): void
    {
        Schema::table('cargos', function (Blueprint $table) {
            $table->dropColumn('grupo');
        });
    }
};
