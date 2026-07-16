<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cumpleanos', function (Blueprint $table) {
            $table->id();
            $table->string('fecha', 10); // DD/MM
            $table->string('nombre');
            $table->text('detalles')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cumpleanos');
    }
};
