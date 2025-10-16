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
        // Eliminar la tabla usuarios si existe
        Schema::dropIfExists('usuarios');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No se puede revertir esta migración sin tener el esquema original
        // Si necesitas revertir, deberás restaurar desde un backup
        // O crear manualmente la tabla con su estructura original
    }
};
