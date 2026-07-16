<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consulta_historial', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('tipo', 3); // DNI, RUC
            $table->string('documento', 11);
            $table->text('resultado_json')->nullable();
            $table->string('nombre_mostrar')->nullable(); // nombre_completo o razon_social
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consulta_historial');
    }
};
