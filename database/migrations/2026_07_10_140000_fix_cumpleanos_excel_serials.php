<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rows = DB::table('cumpleanos')->get(['id', 'fecha']);

        foreach ($rows as $row) {
            $fecha = $row->fecha;

            // Skip non-serial values (already DD/MM format)
            if (!preg_match('/^\d+$/', $fecha)) {
                continue;
            }

            $serial = (int) $fecha;
            // Excel serial → real date: 1899-12-30 + serial days
            $ts = ($serial - 25569) * 86400;
            if ($ts <= 0) {
                continue;
            }
            $ddmm = date('d/m', $ts);

            DB::table('cumpleanos')->where('id', $row->id)->update(['fecha' => $ddmm]);
        }
    }

    public function down(): void
    {
        // Irreversible — original serials cannot be reconstructed from DD/MM
    }
};
