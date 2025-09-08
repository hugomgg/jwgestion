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
        Schema::create('congregaciones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('direccion')->nullable();
            $table->string('telefono')->nullable();
            $table->string('persona_contacto')->nullable();
            $table->tinyInteger('estado')->default(1); // 1=activo, 0=inactivo
            
            // Campos de auditoría
            $table->unsignedBigInteger('creador_id')->default(1);
            $table->unsignedBigInteger('modificador_id')->default(1);
            $table->timestamp('creado_por_timestamp')->nullable();
            $table->timestamp('modificado_por_timestamp')->nullable();
            
            $table->timestamps();
            
            // Índices para optimizar consultas
            $table->index('estado');
            $table->index('creador_id');
            $table->index('modificador_id');
            $table->index(['estado', 'created_at']);
            $table->index('nombre');
            
            // Claves foráneas para integridad referencial
            $table->foreign('creador_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('modificador_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('congregaciones');
    }
};
