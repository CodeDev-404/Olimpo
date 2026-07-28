<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('control_vehiculos', function (Blueprint $table) {
            $table->id();
            $table->date('fecha')->nullable();
            $table->string('chofer')->default('');
            $table->string('placa')->default('');
            $table->string('marca')->default('');
            $table->string('modelo')->default('');
            $table->string('clase')->default('');
            $table->string('hora_salida')->default('');
            $table->string('km_salida')->default('');
            $table->string('hora_ingreso')->default('');
            $table->string('km_ingreso')->default('');
            $table->text('observacion')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('control_vehiculos');
    }
};
