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
        Schema::table('users', function (Blueprint $table) {
            // Agregar campo nombre_completo como texto opcional después del campo name
            $table->string('nombre_completo')->nullable()->after('name');
        });
        
        // Agregar campo abreviacion a la tabla salas
        if (Schema::hasTable('salas')) {
            Schema::table('salas', function (Blueprint $table) {
                if (!Schema::hasColumn('salas', 'abreviacion')) {
                    $table->string('abreviacion', 10)->nullable()->after('nombre');
                }
            });
            
            // Actualizar los registros existentes con las abreviaciones
            DB::table('salas')->where('id', 1)->update(['abreviacion' => 'SP']);
            DB::table('salas')->where('id', 2)->update(['abreviacion' => 'S1']);
            DB::table('salas')->where('id', 3)->update(['abreviacion' => 'S2']);
            
            // Hacer el campo obligatorio después de asignar valores
            Schema::table('salas', function (Blueprint $table) {
                $table->string('abreviacion', 10)->nullable(false)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Eliminar el campo nombre_completo
            $table->dropColumn('nombre_completo');
        });
        
        if (Schema::hasTable('salas') && Schema::hasColumn('salas', 'abreviacion')) {
            Schema::table('salas', function (Blueprint $table) {
                $table->dropColumn('abreviacion');
            });
        }
    }
};