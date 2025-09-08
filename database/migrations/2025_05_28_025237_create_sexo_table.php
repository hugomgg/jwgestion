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
        Schema::create('sexo', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('descripcion')->nullable();
            $table->tinyInteger('estado')->default(1);
            
            // Campos de auditoría
            $table->unsignedBigInteger('creador_id')->default(1);
            $table->unsignedBigInteger('modificador_id')->default(1);
            $table->timestamp('creado_por_timestamp')->nullable();
            $table->timestamp('modificado_por_timestamp')->nullable();
            
            $table->timestamps();
            
            // Índices para optimizar consultas
            $table->index('creador_id', 'idx_sexo_creador');
            $table->index('modificador_id', 'idx_sexo_modificador');
            $table->index('creado_por_timestamp', 'idx_sexo_creado_timestamp');
            $table->index('modificado_por_timestamp', 'idx_sexo_modificado_timestamp');
            
            // Claves foráneas para integridad referencial
            $table->foreign('creador_id', 'fk_sexo_creador')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('modificador_id', 'fk_sexo_modificador')->references('id')->on('users')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sexo');
    }
};
