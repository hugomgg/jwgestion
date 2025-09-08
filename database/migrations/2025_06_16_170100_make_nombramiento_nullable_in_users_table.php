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
        Schema::table('users', function (Blueprint $table) {
            // Hacer el campo nombramiento nullable y remover el valor por defecto
            $table->unsignedBigInteger('nombramiento')->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Restaurar el campo nombramiento como requerido con valor por defecto
            $table->unsignedBigInteger('nombramiento')->default(3)->change();
        });
    }
};