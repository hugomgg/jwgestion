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
        Schema::create('partes_seccion', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->comment('Nombre de la parte de sección');
            $table->string('abreviacion')->comment('Abreviación de la parte');
            $table->integer('orden')->comment('Orden de la parte en la sección');
            $table->integer('tiempo')->comment('Tiempo en minutos asignado a la parte');
            $table->foreignId('seccion_id')->constrained('secciones_reunion')->onDelete('cascade')->comment('Relación con secciones_reunion');
            $table->foreignId('asignacion_id')->constrained('asignaciones')->onDelete('cascade')->comment('Relación con asignaciones');
            $table->tinyInteger('estado')->default(1)->comment('Estado: 1=Activo, 0=Inactivo');
            $table->unsignedBigInteger('creador_id')->default(1)->comment('ID del usuario que creó el registro');
            $table->unsignedBigInteger('modificador_id')->default(1)->comment('ID del usuario que modificó el registro');
            $table->timestamp('creado_por_timestamp')->nullable()->comment('Timestamp de creación');
            $table->timestamp('modificado_por_timestamp')->nullable()->comment('Timestamp de modificación');
            $table->timestamps();
            
            // Índices para optimizar consultas
            $table->index('estado');
            $table->index('orden');
            $table->index('seccion_id');
            $table->index('asignacion_id');
            $table->index('creador_id');
            $table->index('modificador_id');
            
            // Claves foráneas adicionales
            $table->foreign('creador_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('modificador_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partes_seccion');
    }
};
