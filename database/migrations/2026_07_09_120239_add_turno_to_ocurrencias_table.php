<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ocurrencias', function (Blueprint $table) {
            $table->string('turno', 10)->default('DÍA')->after('observacion');
        });
    }

    public function down(): void
    {
        Schema::table('ocurrencias', function (Blueprint $table) {
            $table->dropColumn('turno');
        });
    }
};
