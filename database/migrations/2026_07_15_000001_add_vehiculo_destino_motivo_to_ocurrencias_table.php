<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ocurrencias', function (Blueprint $table) {
            $table->string('vehiculo')->nullable()->after('tipo');
            $table->string('destino')->nullable()->after('vehiculo');
            $table->string('motivo')->nullable()->after('destino');
        });
    }

    public function down(): void
    {
        Schema::table('ocurrencias', function (Blueprint $table) {
            $table->dropColumn(['vehiculo', 'destino', 'motivo']);
        });
    }
};
