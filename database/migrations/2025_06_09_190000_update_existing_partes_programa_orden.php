<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Actualizar los registros existentes para asignar orden basado en ID
        $programas = DB::table('partes_programa')->select('programa_id')->distinct()->get();
        
        foreach ($programas as $programa) {
            $partes = DB::table('partes_programa')
                ->where('programa_id', $programa->programa_id)
                ->orderBy('id')
                ->get();
            
            $orden = 1;
            foreach ($partes as $parte) {
                DB::table('partes_programa')
                    ->where('id', $parte->id)
                    ->update(['orden' => $orden]);
                $orden++;
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir todos los órdenes a 0
        DB::table('partes_programa')->update(['orden' => 0]);
    }
};