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
        Schema::create('partes_programa', function (Blueprint $table) {
            $table->id();
            
            // Relación obligatoria con la tabla programas
            $table->foreignId('programa_id')->constrained('programas')->onDelete('cascade');
            
            // Relación obligatoria con la tabla partes_seccion
            $table->foreignId('parte_id')->constrained('partes_seccion')->onDelete('cascade');
            
            // Tiempo (entero opcional)
            $table->integer('tiempo')->nullable();
            
            // Relación obligatoria con la tabla asignaciones
            $table->foreignId('asignacion_id')->constrained('asignaciones')->onDelete('cascade');
            
            // Tema (texto opcional)
            $table->text('tema')->nullable();
            
            // Relación obligatoria con la tabla usuarios (encargado)
            $table->foreignId('encargado_id')->constrained('users')->onDelete('cascade');
            
            // Relación opcional con la tabla usuarios (encargado reemplazado)
            $table->foreignId('encargado_reemplazado_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Relación opcional con la tabla usuarios (ayudante)
            $table->foreignId('ayudante_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Relación opcional con la tabla usuarios (ayudante reemplazado)
            $table->foreignId('ayudante_reemplazado_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Lección (texto opcional)
            $table->text('leccion')->nullable();
            
            // Estado
            $table->boolean('estado')->default(true);
            
            // Creador y modificador
            $table->foreignId('creado_por')->constrained('users')->onDelete('cascade');
            $table->foreignId('modificado_por')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partes_programa');
    }
};
