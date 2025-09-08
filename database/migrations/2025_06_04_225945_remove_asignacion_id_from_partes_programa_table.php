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
            // Eliminar la clave foránea primero
            $table->dropForeign(['asignacion_id']);
            // Luego eliminar la columna
            $table->dropColumn('asignacion_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partes_programa', function (Blueprint $table) {
            // Recrear la columna
            $table->foreignId('asignacion_id')->constrained('asignaciones')->onDelete('cascade');
        });
    }
};
