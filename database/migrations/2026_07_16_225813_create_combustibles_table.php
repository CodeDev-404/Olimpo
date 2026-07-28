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
        Schema::create('combustibles', function (Blueprint $table) {
            $table->id();
            $table->date('fecha')->nullable();
            $table->string('categoria')->default('');
            $table->string('clase')->default('');
            $table->string('marca')->default('');
            $table->string('placa')->default('');
            $table->string('modelo')->default('');
            $table->string('anio')->default('');
            $table->string('color')->default('');
            $table->string('conductor')->default('');
            $table->string('kilometraje')->default('');
            $table->string('combustible')->default('');
            $table->decimal('galones', 10, 2)->default(0);
            $table->decimal('precio_galon', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('combustibles');
    }
};
