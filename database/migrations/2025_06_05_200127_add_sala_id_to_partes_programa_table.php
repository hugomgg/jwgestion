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
            // Agregar el campo sala_id obligatorio con valor por defecto 1
            $table->foreignId('sala_id')->default(1)->constrained('salas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partes_programa', function (Blueprint $table) {
            // Eliminar la columna sala_id
            $table->dropForeign(['sala_id']);
            $table->dropColumn('sala_id');
        });
    }
};
