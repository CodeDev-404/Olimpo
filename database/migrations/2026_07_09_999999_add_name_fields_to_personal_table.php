<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personal', function (Blueprint $table) {
            $table->string('segundo_nombre')->default('')->after('nombre');
            $table->string('apellido_paterno')->default('')->after('segundo_nombre');
            $table->string('apellido_materno')->default('')->after('apellido_paterno');
        });
    }

    public function down(): void
    {
        Schema::table('personal', function (Blueprint $table) {
            $table->dropColumn(['segundo_nombre', 'apellido_paterno', 'apellido_materno']);
        });
    }
};
