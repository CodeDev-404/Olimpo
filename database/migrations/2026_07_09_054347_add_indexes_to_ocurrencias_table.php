<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ocurrencias', function (Blueprint $table) {
            $table->index(['fecha', 'persona_nombre']);
            $table->index(['mes', 'anio']);
            $table->index('tipo');
        });
    }

    public function down(): void
    {
        Schema::table('ocurrencias', function (Blueprint $table) {
            $table->dropIndex(['fecha', 'persona_nombre']);
            $table->dropIndex(['mes', 'anio']);
            $table->dropIndex(['tipo']);
        });
    }
};
