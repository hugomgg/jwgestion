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
        Schema::table('informes', function (Blueprint $table) {
            // Agregar campo congregacion_id después de grupo_id
            $table->unsignedBigInteger('congregacion_id')
                  ->after('grupo_id')
                  ->comment('Congregación a la que pertenece el informe');
            
            // Agregar clave foránea
            $table->foreign('congregacion_id')
                  ->references('id')
                  ->on('congregaciones')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
            
            // Agregar índice para optimizar consultas
            $table->index('congregacion_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('informes', function (Blueprint $table) {
            // Eliminar clave foránea e índice
            $table->dropForeign(['congregacion_id']);
            $table->dropIndex(['congregacion_id']);
            
            // Eliminar columna
            $table->dropColumn('congregacion_id');
        });
    }
};
