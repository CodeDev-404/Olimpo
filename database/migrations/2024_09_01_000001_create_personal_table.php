<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personal', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('cargo')->default('');
            $table->string('departamento')->default('');
            $table->string('documento')->default('');
            $table->string('telefono')->default('');
            $table->string('email')->default('');
            $table->string('estado')->default('ACTIVO');
            $table->string('hora_entrada')->default('08:00');
            $table->string('hora_salida')->default('17:00');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal');
    }
};
