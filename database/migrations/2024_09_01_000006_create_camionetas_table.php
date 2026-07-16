<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('camionetas', function (Blueprint $table) {
            $table->id();
            $table->string('placa')->unique();
            $table->string('marca')->default('');
            $table->string('modelo')->default('');
            $table->string('anio')->default('');
            $table->string('color')->default('');
            $table->string('estado')->default('ACTIVO');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('camionetas');
    }
};
