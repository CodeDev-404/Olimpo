<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asistencia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('persona_id')->constrained('personal')->cascadeOnDelete();
            $table->string('persona_nombre');
            $table->string('fecha');
            $table->string('hora_entrada')->default('');
            $table->string('hora_salida')->default('');
            $table->integer('tardanza_min')->default(0);
            $table->string('etiqueta')->default('');
            $table->decimal('horas_trabajadas', 5, 1)->default(0);
            $table->timestamps();
            $table->unique(['persona_id', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asistencia');
    }
};
