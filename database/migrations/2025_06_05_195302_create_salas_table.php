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
        Schema::create('salas', function (Blueprint $table) {
            $table->id();
            
            // Nombre de la sala (1=Principal, 2=Auxiliar Núm. 1, 3=Auxiliar Núm. 2)
            $table->tinyInteger('nombre');
            
            // Estado (1=Activo por defecto)
            $table->boolean('estado')->default(true);
            
            // Creador (obligatorio, por defecto=1, relacionar con usuarios)
            $table->foreignId('creador_id')->default(1)->constrained('users')->onDelete('cascade');
            
            // Modificador (obligatorio, por defecto=1, relacionar con usuarios)
            $table->foreignId('modificador_id')->default(1)->constrained('users')->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salas');
    }
};
