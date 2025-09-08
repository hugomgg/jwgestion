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
        // Primero actualizar las asignaciones existentes para tener abreviaciones únicas
        $asignaciones = \App\Models\Asignacion::all();
        foreach ($asignaciones as $index => $asignacion) {
            $asignacion->update([
                'abreviacion' => 'A' . ($index + 1)
            ]);
        }

        // Luego agregar el constraint unique
        Schema::table('asignaciones', function (Blueprint $table) {
            $table->unique('abreviacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asignaciones', function (Blueprint $table) {
            $table->dropUnique(['abreviacion']);
        });
    }
};
