<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Backfill persona_id from persona_nombre for existing records
        $rows = DB::table('ocurrencias')->whereNull('persona_id')->where('persona_nombre', '!=', '')->get();
        foreach ($rows as $r) {
            $persona = DB::table('personal')->where('nombre', $r->persona_nombre)->first();
            if ($persona) {
                DB::table('ocurrencias')->where('id', $r->id)->update(['persona_id' => $persona->id]);
            }
        }
    }

    public function down(): void
    {
        // Cannot safely reverse, but persona_id remains nullable
    }
};
