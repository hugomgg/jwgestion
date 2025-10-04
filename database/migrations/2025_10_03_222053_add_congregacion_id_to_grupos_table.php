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
        Schema::table('grupos', function (Blueprint $table) {
            // Agregar campo congregacion_id después de nombre
            $table->unsignedBigInteger('congregacion_id')->default(1)->after('nombre');
            
            // Crear índice
            $table->index('congregacion_id');
            
            // Crear foreign key constraint
            $table->foreign('congregacion_id')->references('id')->on('congregaciones')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grupos', function (Blueprint $table) {
            // Eliminar la foreign key constraint
            $table->dropForeign(['congregacion_id']);
            
            // Eliminar el índice
            $table->dropIndex(['congregacion_id']);
            
            // Eliminar la columna
            $table->dropColumn('congregacion_id');
        });
    }
};
