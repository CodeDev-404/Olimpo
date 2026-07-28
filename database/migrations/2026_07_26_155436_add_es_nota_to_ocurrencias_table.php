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
        Schema::table('ocurrencias', function (Blueprint $table) {
            $table->boolean('es_nota')->default(false)->after('user_id');
            $table->text('nota_texto')->nullable()->after('es_nota');
        });
    }

    public function down(): void
    {
        Schema::table('ocurrencias', function (Blueprint $table) {
            $table->dropColumn(['es_nota', 'nota_texto']);
        });
    }
};
