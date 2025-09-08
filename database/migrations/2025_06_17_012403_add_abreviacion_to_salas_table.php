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
        // Verificar si la columna ya existe
        if (!Schema::hasColumn('salas', 'abreviacion')) {
            // Agregar el campo abreviacion como nullable inicialmente
            Schema::table('salas', function (Blueprint $table) {
                $table->string('abreviacion')->nullable()->after('nombre');
            });
            
            // Asignar valores a los registros existentes
            DB::table('salas')->where('id', 1)->update(['abreviacion' => 'SP']);
            DB::table('salas')->where('id', 2)->update(['abreviacion' => 'S1']);
            DB::table('salas')->where('id', 3)->update(['abreviacion' => 'S2']);
            
            // Hacer el campo obligatorio después de asignar los valores
            Schema::table('salas', function (Blueprint $table) {
                $table->string('abreviacion')->nullable(false)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salas', function (Blueprint $table) {
            $table->dropColumn('abreviacion');
        });
    }
};
