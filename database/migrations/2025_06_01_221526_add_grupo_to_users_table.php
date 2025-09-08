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
            $table->unsignedBigInteger('grupo')->default(1)->after('congregacion');
            
            // Índice para mejorar el rendimiento
            $table->index('grupo');
            
            // Foreign key constraint (opcional, comentado por si no existe aún la tabla grupos)
            // $table->foreign('grupo')->references('id')->on('grupos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['grupo']);
            $table->dropColumn('grupo');
        });
    }
};
