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
            // Renombrar creado_por a creador_id
            $table->renameColumn('creado_por', 'creador_id');
            // Renombrar modificado_por a modificador_id
            $table->renameColumn('modificado_por', 'modificador_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partes_programa', function (Blueprint $table) {
            // Revertir los cambios
            $table->renameColumn('creador_id', 'creado_por');
            $table->renameColumn('modificador_id', 'modificado_por');
        });
    }
};
