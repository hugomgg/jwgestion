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
        Schema::create('informes', function (Blueprint $table) {
            $table->id();
            
            // Campos principales
            $table->integer('anio')->comment('Año del informe');
            $table->integer('mes')->comment('Mes del informe (1-12)');
            
            // Relaciones con otras tablas
            $table->unsignedBigInteger('user_id')->comment('Usuario que reporta');
            $table->unsignedBigInteger('grupo_id')->comment('Grupo al que pertenece');
            $table->unsignedBigInteger('servicio_id')->comment('Servicio que realiza');
            
            // Datos del informe
            $table->boolean('participa')->default(true)->comment('Participó en el servicio');
            $table->integer('cantidad_estudios')->default(0)->comment('Cantidad de estudios bíblicos');
            $table->integer('horas')->nullable()->comment('Horas de servicio');
            $table->text('comentario')->nullable()->comment('Comentarios adicionales');
            
            // Estado
            $table->tinyInteger('estado')->default(1)->comment('1: Habilitado, 0: Deshabilitado');
            
            // Campos de auditoría (según patrón del proyecto)
            // Nota: Los valores por defecto los asigna el trait Auditable automáticamente
            $table->unsignedBigInteger('creador_id')->nullable()->comment('Usuario que creó el registro');
            $table->unsignedBigInteger('modificador_id')->nullable()->comment('Usuario que modificó el registro');
            
            // Timestamps
            $table->timestamps();
            
            // Definir claves foráneas
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
                  
            $table->foreign('grupo_id')
                  ->references('id')
                  ->on('grupos')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
                  
            $table->foreign('servicio_id')
                  ->references('id')
                  ->on('servicios')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
                  
            $table->foreign('creador_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null')
                  ->onUpdate('cascade');
                  
            $table->foreign('modificador_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null')
                  ->onUpdate('cascade');
            
            // Índices para mejorar el rendimiento de consultas
            $table->index('anio');
            $table->index('mes');
            $table->index(['user_id', 'anio', 'mes']);
            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('informes');
    }
};
