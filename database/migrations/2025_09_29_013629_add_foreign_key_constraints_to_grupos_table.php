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
        // Simplemente intentar añadir las restricciones - si ya existen, ignorar el error
        try {
            Schema::table('grupos', function (Blueprint $table) {
                $table->foreign('creador_id')->references('id')->on('users')->onDelete('restrict');
            });
        } catch (\Exception $e) {
            // Ignorar si la restricción ya existe
        }

        try {
            Schema::table('grupos', function (Blueprint $table) {
                $table->foreign('modificador_id')->references('id')->on('users')->onDelete('restrict');
            });
        } catch (\Exception $e) {
            // Ignorar si la restricción ya existe
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grupos', function (Blueprint $table) {
            try {
                $table->dropForeign(['creador_id']);
            } catch (\Exception $e) {
                // Ignorar si no existe
            }

            try {
                $table->dropForeign(['modificador_id']);
            } catch (\Exception $e) {
                // Ignorar si no existe
            }
        });
    }
};
