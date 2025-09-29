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
            // Eliminar la foreign key constraint primero
            $table->dropForeign(['congregacion_id']);
            // Eliminar el índice
            $table->dropIndex(['congregacion_id']);
            // Eliminar la columna
            $table->dropColumn('congregacion_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grupos', function (Blueprint $table) {
            // Recrear la columna
            $table->unsignedBigInteger('congregacion_id')->after('id');
            // Crear el índice
            $table->index('congregacion_id');
            // Crear la foreign key constraint
            $table->foreign('congregacion_id')->references('id')->on('congregaciones');
        });
    }
};
