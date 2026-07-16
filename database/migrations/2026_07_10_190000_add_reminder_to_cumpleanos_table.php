<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cumpleanos', function (Blueprint $table) {
            $table->boolean('recordatorio_activo')->default(true)->after('parentesco');
            $table->time('recordatorio_hora')->default('07:30:00')->after('recordatorio_activo');
        });
    }

    public function down(): void
    {
        Schema::table('cumpleanos', function (Blueprint $table) {
            $table->dropColumn(['recordatorio_activo', 'recordatorio_hora']);
        });
    }
};