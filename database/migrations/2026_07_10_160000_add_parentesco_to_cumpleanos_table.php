<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cumpleanos', function (Blueprint $table) {
            $table->string('parentesco', 50)->nullable()->after('nombre');
        });
    }

    public function down(): void
    {
        Schema::table('cumpleanos', function (Blueprint $table) {
            $table->dropColumn('parentesco');
        });
    }
};