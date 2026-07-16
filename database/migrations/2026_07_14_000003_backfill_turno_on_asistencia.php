<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('asistencia')
            ->whereNull('turno')
            ->orWhere('turno', '')
            ->update(['turno' => 'DÍA']);
    }

    public function down(): void
    {
        // No safe reverse — turno column default handles new records
    }
};
