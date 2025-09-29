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
            // Renombrar campos
            $table->renameColumn('creador', 'creador_id');
            $table->renameColumn('modificador', 'modificador_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grupos', function (Blueprint $table) {
            // Revertir el renombrado
            $table->renameColumn('creador_id', 'creador');
            $table->renameColumn('modificador_id', 'modificador');
        });
    }
};
