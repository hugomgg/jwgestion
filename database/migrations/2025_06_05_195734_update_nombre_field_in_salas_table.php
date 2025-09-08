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
        // Primero actualizamos los datos existentes
        DB::table('salas')->where('nombre', 1)->update(['nombre' => 'Principal']);
        DB::table('salas')->where('nombre', 2)->update(['nombre' => 'Auxiliar Núm. 1']);
        DB::table('salas')->where('nombre', 3)->update(['nombre' => 'Auxiliar Núm. 2']);
        
        // Luego cambiamos el tipo de campo
        Schema::table('salas', function (Blueprint $table) {
            $table->text('nombre')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Primero cambiamos el tipo de campo
        Schema::table('salas', function (Blueprint $table) {
            $table->tinyInteger('nombre')->change();
        });
        
        // Luego revertimos los datos
        DB::table('salas')->where('nombre', 'Principal')->update(['nombre' => 1]);
        DB::table('salas')->where('nombre', 'Auxiliar Núm. 1')->update(['nombre' => 2]);
        DB::table('salas')->where('nombre', 'Auxiliar Núm. 2')->update(['nombre' => 3]);
    }
};
