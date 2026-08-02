<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recordatorios_programados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cumpleano_id')->constrained('cumpleanos')->cascadeOnDelete();
            $table->date('fecha');
            $table->time('hora');
            $table->boolean('enviado')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recordatorios_programados');
    }
};
