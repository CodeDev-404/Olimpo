<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ocurrencias', function (Blueprint $table) {
            $table->id();
            $table->string('fecha');
            $table->string('hora_ingreso')->default('');
            $table->string('hora_salida')->default('');
            $table->foreignId('persona_id')->nullable()->constrained('personal')->nullOnDelete();
            $table->string('persona_nombre');
            $table->string('tipo');
            $table->string('otro')->default('');
            $table->text('detalles')->nullable();
            $table->text('observacion')->nullable();
            $table->integer('mes')->nullable();
            $table->integer('anio')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ocurrencias');
    }
};
