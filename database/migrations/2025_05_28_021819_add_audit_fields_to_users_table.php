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
            // Campos de auditoría
            $table->unsignedBigInteger('creador_id')->default(1)->after('estado');
            $table->unsignedBigInteger('modificador_id')->default(1)->after('creador_id');
            $table->timestamp('creado_por_timestamp')->nullable()->after('modificador_id');
            $table->timestamp('modificado_por_timestamp')->nullable()->after('creado_por_timestamp');
            
            // Índices para optimizar consultas
            $table->index('creador_id', 'idx_users_creador');
            $table->index('modificador_id', 'idx_users_modificador');
            $table->index('creado_por_timestamp', 'idx_users_creado_timestamp');
            $table->index('modificado_por_timestamp', 'idx_users_modificado_timestamp');
            
            // Claves foráneas para integridad referencial
            $table->foreign('creador_id', 'fk_users_creador')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('modificador_id', 'fk_users_modificador')->references('id')->on('users')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Eliminar claves foráneas
            $table->dropForeign('fk_users_creador');
            $table->dropForeign('fk_users_modificador');
            
            // Eliminar índices
            $table->dropIndex('idx_users_creador');
            $table->dropIndex('idx_users_modificador');
            $table->dropIndex('idx_users_creado_timestamp');
            $table->dropIndex('idx_users_modificado_timestamp');
            
            // Eliminar columnas
            $table->dropColumn([
                'creador_id',
                'modificador_id',
                'creado_por_timestamp',
                'modificado_por_timestamp'
            ]);
        });
    }
};
