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
        Schema::table('partes_seccion', function (Blueprint $table) {
            $table->unique('abreviacion', 'partes_seccion_abreviacion_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partes_seccion', function (Blueprint $table) {
            $table->dropUnique('partes_seccion_abreviacion_unique');
        });
    }
};
