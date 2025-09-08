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
        Schema::table('partes_programa', function (Blueprint $table) {
            // Agregar el campo orden después del campo parte_id
            $table->integer('orden')->default(0)->after('parte_id');
            
            // Agregar índice para mejorar el rendimiento de consultas ordenadas
            $table->index(['programa_id', 'orden']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partes_programa', function (Blueprint $table) {
            // Eliminar el índice primero
            $table->dropIndex(['programa_id', 'orden']);
            
            // Eliminar la columna orden
            $table->dropColumn('orden');
        });
    }
};