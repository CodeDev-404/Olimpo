<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rows = DB::table('cumpleanos')->get(['id', 'nombre', 'detalles', 'parentesco']);

        foreach ($rows as $row) {
            $data = [];
            
            if (!empty($row->nombre)) {
                $data['nombre'] = ucwords(strtolower($row->nombre));
            }
            if (!empty($row->detalles)) {
                $data['detalles'] = ucwords(strtolower($row->detalles));
            }
            if (!empty($row->parentesco)) {
                $data['parentesco'] = ucwords(strtolower($row->parentesco));
            }
            
            if (!empty($data)) {
                DB::table('cumpleanos')->where('id', $row->id)->update($data);
            }
        }
    }

    public function down(): void
    {
        // No revert - capitalization is one-way
    }
};